import React, { useEffect, useRef, useState } from 'react';
import { Navigate, useLocation, useParams } from 'react-router-dom';
import { toast } from 'react-toastify';
import { ArrowLeft, Award, BookOpen, CheckCircle2, ChevronDown, ChevronUp, Clock, FileText, Loader2, Lock, PlayCircle, ShieldCheck, User } from 'lucide-react';
import { dataService } from '../../services/dataService';
import { useAuth } from '../../contexts/AuthContext';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useCurrentRouteBootstrap } from '../../contexts/RouteBootstrapContext';
import AppLink from '../common/AppLink';

type Target = { type: 'lesson' | 'exam'; id: number } | null;

const pickTarget = (course: any, search: string): Target => {
    const params = new URLSearchParams(search);
    const lessonId = Number(params.get('lesson'));
    const examId = Number(params.get('exam'));
    if (lessonId) {
        for (const module of course?.modules || []) {
            const lesson = module.lessons?.find((item: any) => item.id === lessonId);
            if (lesson?.is_unlocked) return { type: 'lesson', id: lesson.id };
        }
    }
    if (examId) {
        for (const module of course?.modules || []) {
            if (module.exam?.id === examId && module.exam?.is_unlocked) return { type: 'exam', id: module.exam.id };
        }
    }
    return course?.resume_target || null;
};

const findLesson = (course: any, lessonId?: number | null) => {
    for (const module of course?.modules || []) {
        const lesson = module.lessons?.find((item: any) => item.id === lessonId);
        if (lesson) return { module, lesson };
    }
    return null;
};

const findExam = (course: any, examId?: number | null) => {
    for (const module of course?.modules || []) {
        if (module.exam?.id === examId) return { module, exam: module.exam };
    }
    return null;
};

const fmt = (n?: number | null) => {
    if (n == null) return null;

    const totalSeconds = Math.max(0, Math.floor(n));
    const minutes = Math.floor(totalSeconds / 60);
    const seconds = totalSeconds % 60;

    return `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};

const normalizeCoursePayload = (course: any) => {
    if (!course?.modules) return course;

    let previousModuleSatisfied = true;
    let nextTarget: Target = null;
    let totalItems = 0;
    let completedItems = 0;

    const modules = (course.modules || []).map((module: any, moduleIndex: number) => {
        const moduleUnlocked = moduleIndex === 0 ? true : previousModuleSatisfied;
        let nextLessonUnlocked = moduleUnlocked;

        const lessons = (module.lessons || []).map((lesson: any) => {
            const isCompleted = Boolean(lesson.is_completed);
            const isUnlocked = Boolean(moduleUnlocked && nextLessonUnlocked);

            totalItems++;

            if (isCompleted) {
                completedItems++;
            } else if (!nextTarget && isUnlocked) {
                nextTarget = { type: 'lesson', id: lesson.id };
            }

            nextLessonUnlocked = Boolean(nextLessonUnlocked && isCompleted);

            return {
                ...lesson,
                is_completed: isCompleted,
                is_unlocked: isUnlocked,
            };
        });

        const allLessonsCompleted = lessons.every((lesson: any) => lesson.is_completed);
        const exam = module.exam ? {
            ...module.exam,
            is_unlocked: Boolean(moduleUnlocked && allLessonsCompleted),
            is_passed: Boolean(module.exam.is_passed),
        } : null;

        if (exam) {
            totalItems++;

            if (exam.is_passed) {
                completedItems++;
            } else if (!nextTarget && exam.is_unlocked) {
                nextTarget = { type: 'exam', id: exam.id };
            }
        }

        const moduleCompleted = Boolean(allLessonsCompleted && (exam ? exam.is_passed : true));
        previousModuleSatisfied = Boolean(exam ? exam.is_passed : allLessonsCompleted);

        return {
            ...module,
            is_unlocked: moduleUnlocked,
            is_completed: moduleCompleted,
            lessons_count: lessons.length,
            lessons,
            exam,
        };
    });

    const total = totalItems || 0;
    const percent = total > 0 ? Math.round((completedItems / total) * 100) : 0;
    const currentResumeTarget = course.resume_target;
    const currentResumeStillValid = currentResumeTarget?.type === 'lesson'
        ? modules.some((module: any) => module.lessons?.some((lesson: any) => lesson.id === currentResumeTarget.id && lesson.is_unlocked))
        : currentResumeTarget?.type === 'exam'
            ? modules.some((module: any) => module.exam?.id === currentResumeTarget.id && module.exam?.is_unlocked)
            : false;

    return {
        ...course,
        modules,
        progress: {
            ...course.progress,
            completed_items: completedItems,
            total_items: total,
            percent,
        },
        resume_target: currentResumeStillValid ? currentResumeTarget : nextTarget,
    };
};

const LearnCoursePage: React.FC = () => {
    const { courseSlug } = useParams<{ courseSlug: string }>();
    const { user } = useAuth();
    const { __, t } = useTranslation();
    const location = useLocation();
    const initialBootstrap = useCurrentRouteBootstrap<any>();
    const bootstrapLearningCourse = initialBootstrap?.learningCourse ?? null;
    const videoRef = useRef<HTMLVideoElement | null>(null);
    const bootstrapCourseRef = useRef<any>(bootstrapLearningCourse);
    const courseRef = useRef<any>(bootstrapCourseRef.current ? normalizeCoursePayload(bootstrapCourseRef.current) : null);
    const openedLessonRef = useRef<number | null>(null);
    const [urlSearch, setUrlSearch] = useState<string>(() => window.location.search || location.search);
    const [course, setCourse] = useState<any>(() => bootstrapCourseRef.current ? normalizeCoursePayload(bootstrapCourseRef.current) : null);
    const [loading, setLoading] = useState(() => !bootstrapCourseRef.current);
    const [error, setError] = useState<string | null>(null);
    const [target, setTarget] = useState<Target>(() => {
        const initialCourse = bootstrapCourseRef.current ? normalizeCoursePayload(bootstrapCourseRef.current) : null;
        return initialCourse ? pickTarget(initialCourse, window.location.search || location.search) : null;
    });
    const [examAttempt, setExamAttempt] = useState<any | null>(null);
    const [answers, setAnswers] = useState<Record<number, number>>({});
    const [actionBusy, setActionBusy] = useState<'lesson' | 'startExam' | 'submitExam' | 'resetExam' | null>(null);
    const [expandedModules, setExpandedModules] = useState<Record<number, boolean>>({});

    const applyCoursePayload = (payload: any, search = urlSearch) => {
        const normalizedPayload = normalizeCoursePayload(payload);
        courseRef.current = normalizedPayload;
        setCourse(normalizedPayload);
        setTarget(pickTarget(normalizedPayload, search));
        setError(null);
        setLoading(false);

        return normalizedPayload;
    };

    useEffect(() => {
        courseRef.current = course;
    }, [course]);

    useEffect(() => {
        if (!bootstrapLearningCourse || course) return;

        const normalizedBootstrapCourse = normalizeCoursePayload(bootstrapLearningCourse);
        bootstrapCourseRef.current = null;
        courseRef.current = normalizedBootstrapCourse;
        setCourse(normalizedBootstrapCourse);
        setTarget(pickTarget(normalizedBootstrapCourse, urlSearch));
        setError(null);
        setLoading(false);
    }, [bootstrapLearningCourse, course, urlSearch]);

    useEffect(() => {
        if (!courseSlug) return;

        if (bootstrapCourseRef.current) {
            const bootstrapCourse = normalizeCoursePayload(bootstrapCourseRef.current);
            bootstrapCourseRef.current = null;
            setCourse(bootstrapCourse);
            setTarget(pickTarget(bootstrapCourse, urlSearch));
            setLoading(false);
            return;
        }

        if (!courseRef.current) {
            setLoading(true);
        }

        dataService.getLearningCourse(courseSlug).then((data) => {
            if (!data) {
                if (!courseRef.current) {
                    setCourse(null);
                    setError(__('Unable to load this learning course right now.'));
                }

                return;
            }

            applyCoursePayload(data);
        }).catch(() => {
            if (!courseRef.current) {
                setCourse(null);
                setError(__('Unable to load this learning course right now.'));
            }
        }).finally(() => {
            if (!courseRef.current) {
                setLoading(false);
            }
        });
    }, [courseSlug]);

    useEffect(() => {
        const syncFromBrowser = () => {
            setUrlSearch(window.location.search || '');
        };

        window.addEventListener('popstate', syncFromBrowser);

        return () => window.removeEventListener('popstate', syncFromBrowser);
    }, []);

    useEffect(() => {
        setUrlSearch(window.location.search || location.search);
    }, [location.pathname, location.search]);

    useEffect(() => {
        if (!course) return;
        setTarget(pickTarget(course, urlSearch));
    }, [course, urlSearch]);

    const lessonState = target?.type === 'lesson' ? findLesson(course, target.id) : null;
    const standaloneExamState = target?.type === 'exam' ? findExam(course, target.id) : null;
    const relatedLessonExamState = lessonState?.module?.exam ? { module: lessonState.module, exam: lessonState.module.exam } : null;
    const activeExamState = target?.type === 'exam' ? standaloneExamState : relatedLessonExamState;

    useEffect(() => {
        const activeModuleId = lessonState?.module?.id ?? standaloneExamState?.module?.id;

        setExpandedModules((current) => {
            if (activeModuleId) {
                if (current[activeModuleId]) return current;

                return { ...current, [activeModuleId]: true };
            }

            if (Object.keys(current).length > 0 || !course?.modules?.length) {
                return current;
            }

            const firstModuleId = course.modules[0]?.id;

            return firstModuleId ? { [firstModuleId]: true } : current;
        });
    }, [course?.modules, lessonState?.module?.id, standaloneExamState?.module?.id]);

    useEffect(() => {
        const lessonId = lessonState?.lesson?.id;

        if (!lessonId || openedLessonRef.current === lessonId) return;

        openedLessonRef.current = lessonId;
        dataService.openLearningLesson(lessonId).then((data) => data?.modules && applyCoursePayload(data)).catch(() => {});
    }, [lessonState?.lesson?.id]);

    useEffect(() => {
        const exam = activeExamState?.exam;

        if (!exam) {
            setExamAttempt(null);
            setAnswers({});
            return;
        }

        if (exam.active_attempt_id) {
            let secs: number | null = null;

            if (exam.time_limit_minutes && exam.started_at) {
                const expiresAt = new Date(exam.started_at).getTime() + exam.time_limit_minutes * 60000;
                secs = Math.max(0, Math.floor((expiresAt - Date.now()) / 1000));
            }
            setExamAttempt({ id: exam.active_attempt_id, time_remaining_seconds: secs });
        } else {
            setExamAttempt(null);
        }

        setAnswers({});
    }, [
        activeExamState?.exam?.id,
        activeExamState?.exam?.active_attempt_id,
        activeExamState?.exam?.time_limit_minutes,
        activeExamState?.exam?.started_at,
    ]);

    useEffect(() => {
        if (!examAttempt?.time_remaining_seconds || examAttempt.time_remaining_seconds <= 0) return;
        const timer = window.setInterval(() => setExamAttempt((current: any) => current?.time_remaining_seconds > 0 ? { ...current, time_remaining_seconds: current.time_remaining_seconds - 1 } : current), 1000);
        return () => window.clearInterval(timer);
    }, [examAttempt?.id, examAttempt?.time_remaining_seconds]);

    const syncVideo = async () => {
        if (!lessonState?.lesson?.id || !videoRef.current) return;
        const seconds = Math.floor(videoRef.current.currentTime || 0);
        if (seconds > 0) await dataService.openLearningLesson(lessonState.lesson.id, seconds).catch(() => {});
    };

    const selectTarget = (next: Target, options?: { replace?: boolean }) => {
        if (!next) return;
        const nextSearch = next.type === 'lesson' ? `?lesson=${next.id}` : `?exam=${next.id}`;

        if (next.type === 'lesson') {
            const nextLessonState = findLesson(course, next.id);
            if (nextLessonState?.module?.id) {
                setExpandedModules((current) => ({ ...current, [nextLessonState.module.id]: true }));
            }
        } else {
            const nextExamState = findExam(course, next.id);
            if (nextExamState?.module?.id) {
                setExpandedModules((current) => ({ ...current, [nextExamState.module.id]: true }));
            }
        }

        setTarget(next);
        setUrlSearch(nextSearch);

        const nextUrl = `${window.location.pathname}${nextSearch}`;

        if (options?.replace) {
            window.history.replaceState(window.history.state, '', nextUrl);
        } else {
            window.history.pushState(window.history.state, '', nextUrl);
        }
    };

    const completeLesson = async () => {
        if (!lessonState?.lesson?.id) return;
        setActionBusy('lesson');
        try {
            const payload = await dataService.completeLearningLesson(lessonState.lesson.id);
            const normalizedPayload = applyCoursePayload(payload);
            if (normalizedPayload?.resume_target) {
                selectTarget(normalizedPayload.resume_target, { replace: true });
            }
            toast.success(__('Lesson completed successfully.'));
        } catch (err: any) {
            toast.error(err?.response?.data?.message || __('Unable to complete this lesson right now.'));
        } finally {
            setActionBusy(null);
        }
    };

    const startExam = async () => {
        if (!activeExamState?.exam?.id) return;
        setActionBusy('startExam');
        try {
            const payload = await dataService.startLearningExam(activeExamState.exam.id);
            if (payload?.course?.modules) {
                applyCoursePayload(payload.course);
            }
            setExamAttempt(payload.attempt);
            setAnswers({});
        } catch (err: any) {
            toast.error(err?.response?.data?.message || __('Unable to start the exam right now.'));
        } finally {
            setActionBusy(null);
        }
    };

    const submitExam = async () => {
        if (!activeExamState?.exam?.id || !examAttempt?.id) return;
        const totalQuestions = (activeExamState.exam.questions || []).length;
        const answeredQuestions = (activeExamState.exam.questions || []).filter((question: any) => Boolean(answers[question.id])).length;

        if (totalQuestions > 0 && answeredQuestions < totalQuestions) {
            toast.error(__('Please answer all questions before submitting the exam.'));
            return;
        }

        setActionBusy('submitExam');
        try {
            const payload = await dataService.submitLearningExam(activeExamState.exam.id, examAttempt.id, answers);
            const normalizedCourse = applyCoursePayload(payload.course);
            setExamAttempt(null);
            setAnswers({});

            if (target?.type === 'exam') {
                selectTarget(normalizedCourse.resume_target || target, { replace: true });
            }

            toast.success(payload.attempt?.passed ? __('Exam passed successfully.') : __('Exam submitted successfully.'));
        } catch (err: any) {
            toast.error(err?.response?.data?.message || __('Unable to submit the exam right now.'));
        } finally {
            setActionBusy(null);
        }
    };

    const resetExam = async () => {
        if (!activeExamState?.exam?.id) return;
        setActionBusy('resetExam');
        try {
            const payload = await dataService.resetLearningExam(activeExamState.exam.id);
            const normalizedCourse = applyCoursePayload(payload);
            setExamAttempt(null);
            setAnswers({});

            if (normalizedCourse?.resume_target) {
                selectTarget(normalizedCourse.resume_target, { replace: true });
            }

            toast.success(__('Exam reset successfully. Please review the module again.'));
        } catch (err: any) {
            toast.error(err?.response?.data?.message || __('Unable to reset the exam right now.'));
        } finally {
            setActionBusy(null);
        }
    };

    const renderExamPanel = (state: any, variant: 'inline' | 'standalone' = 'inline') => {
        if (!state?.exam) return null;

        const { exam, module } = state;
        const isInline = variant === 'inline';
        const totalQuestions = (exam.questions || []).length;
        const answeredQuestions = (exam.questions || []).filter((question: any) => Boolean(answers[question.id])).length;
        const isExamFullyAnswered = totalQuestions > 0 && answeredQuestions === totalQuestions;

        return (
            <section className={`rounded-2xl border ${isInline ? 'border-amber-100 dark:border-amber-900/40 bg-amber-50/60 dark:bg-[#171d2e]' : 'border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900'} p-4 sm:p-5`}>
                <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-5">
                    <div>
                        <div className="inline-flex items-center gap-2 rounded-xl bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs font-semibold text-amber-700 dark:text-amber-300 mb-2">
                            <BookOpen className="w-4 h-4" />
                            {isInline ? __('Module Assessment') : __('Course Assessment')}
                        </div>
                        <p className="text-sm font-medium text-amber-700 dark:text-amber-300">{t(module.title)}</p>
                        <h3 className="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-1">{t(exam.title)}</h3>
                        <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">
                            {exam.question_count} {__('Questions')} • {__('Pass Mark')}: {exam.pass_mark}%
                        </p>
                    </div>
                    {examAttempt?.time_remaining_seconds !== null && examAttempt?.time_remaining_seconds !== undefined && (
                        <div className="inline-flex items-center gap-2 rounded-2xl bg-white/80 dark:bg-gray-900/80 px-4 py-2 text-sm font-medium text-amber-700 dark:text-amber-300 border border-amber-100 dark:border-amber-900/40">
                            <Clock className="w-4 h-4" />
                            {fmt(examAttempt.time_remaining_seconds)}
                        </div>
                    )}
                </div>

                {!exam.is_unlocked ? (
                    <div className="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white/70 dark:bg-gray-900/50 p-5">
                        <div className="flex items-start gap-4">
                            <div className="w-11 h-11 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 dark:text-gray-400 shrink-0">
                                <Lock className="w-5 h-5" />
                            </div>
                            <div>
                                <h4 className="text-base font-semibold text-gray-900 dark:text-white mb-1">{__('Complete this module first')}</h4>
                                <p className="text-sm text-gray-500 dark:text-gray-400">{__('Finish all lessons in this module to unlock the assessment below the video player.')}</p>
                            </div>
                        </div>
                    </div>
                ) : exam.is_passed ? (
                    <div className="rounded-xl border border-emerald-200 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-900/20 p-5">
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <div className="flex items-center gap-3 text-emerald-700 dark:text-emerald-300 mb-2">
                                    <ShieldCheck className="w-5 h-5" />
                                    <span className="font-semibold">{__('Exam passed successfully.')}</span>
                                </div>
                                <p className="text-sm text-emerald-800 dark:text-emerald-200">{__('Best Score')}: {exam.best_score ?? exam.latest_score ?? exam.pass_mark}%</p>
                            </div>
                            {course?.certificate?.available && course?.certificate?.download_url ? (
                                <a
                                    href={course.certificate.download_url}
                                    className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-950 text-sm font-semibold text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800"
                                >
                                    <Award className="w-4 h-4" />
                                    {__('Download Certificate')}
                                </a>
                            ) : course?.certificate?.status === 'locked_real_name' ? (
                                <AppLink
                                    to="/dashboard?tab=profile"
                                    className="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-950 text-sm font-semibold text-brand-700 dark:text-brand-300 border border-brand-200 dark:border-brand-800"
                                >
                                    <User className="w-4 h-4" />
                                    {__('Set Real Name')}
                                </AppLink>
                            ) : null}
                        </div>
                    </div>
                ) : exam.attempts_exhausted ? (
                    <div className="rounded-xl border border-rose-200 dark:border-rose-800 bg-rose-50 dark:bg-rose-900/20 p-5">
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <div>
                                <p className="text-base font-semibold text-rose-700 dark:text-rose-300">{__('All exam attempts have been used.')}</p>
                                <p className="text-sm text-rose-700/90 dark:text-rose-300/90 mt-2">{__('Reset this assessment to clear your attempts and require watching this module lessons again before retrying.')}</p>
                            </div>
                            <button type="button" onClick={resetExam} disabled={actionBusy === 'resetExam'} className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-rose-600 to-red-600 text-white font-medium disabled:opacity-60">
                                {actionBusy === 'resetExam' ? <Loader2 className="w-4 h-4 animate-spin" /> : <ShieldCheck className="w-4 h-4" />}
                                {__('Reset Module Progress')}
                            </button>
                        </div>
                    </div>
                ) : !examAttempt ? (
                    <div className="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/70 p-5">
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-3 mb-5">
                            <div className="rounded-xl bg-gray-50 dark:bg-gray-950/70 border border-gray-100 dark:border-gray-800 px-4 py-3">
                                <p className="text-xs text-gray-500 dark:text-gray-400">{__('Attempts Used')}</p>
                                <p className="text-lg font-bold text-gray-900 dark:text-white mt-1">{exam.attempts_used}</p>
                            </div>
                            <div className="rounded-xl bg-gray-50 dark:bg-gray-950/70 border border-gray-100 dark:border-gray-800 px-4 py-3">
                                <p className="text-xs text-gray-500 dark:text-gray-400">{__('Attempts Remaining')}</p>
                                <p className="text-lg font-bold text-gray-900 dark:text-white mt-1">{exam.attempts_remaining ?? __('Unlimited')}</p>
                            </div>
                            <div className="rounded-xl bg-gray-50 dark:bg-gray-950/70 border border-gray-100 dark:border-gray-800 px-4 py-3">
                                <p className="text-xs text-gray-500 dark:text-gray-400">{__('Best Score')}</p>
                                <p className="text-lg font-bold text-gray-900 dark:text-white mt-1">{exam.best_score ?? exam.latest_score ?? '--'}{(exam.best_score !== null && exam.best_score !== undefined) || (exam.latest_score !== null && exam.latest_score !== undefined) ? '%' : ''}</p>
                                {exam.latest_result_status && (
                                    <p className="text-[11px] text-gray-500 dark:text-gray-400 mt-1">
                                        {__('Latest Result')}: {exam.latest_result_status === 'timed_out' ? __('Timed Out') : exam.latest_result_status === 'passed' ? __('Passed') : __('Failed')}
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                            <p className="text-sm text-gray-500 dark:text-gray-400">{__('Pass this assessment to unlock the next module and complete your course progress.')}</p>
                            <button type="button" onClick={startExam} disabled={exam.attempts_remaining === 0 || actionBusy === 'startExam' || exam.attempts_exhausted} className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-white font-medium disabled:opacity-60">
                                {actionBusy === 'startExam' ? <Loader2 className="w-4 h-4 animate-spin" /> : <BookOpen className="w-4 h-4" />}
                                {__('Start Exam')}
                            </button>
                        </div>
                    </div>
                ) : (
                    <div className="space-y-4">
                        {(exam.questions || []).length === 0 ? (
                            <div className="rounded-xl border border-dashed border-gray-300 dark:border-gray-700 bg-white/70 dark:bg-gray-900/50 p-5 text-sm text-gray-500 dark:text-gray-400">
                                {__('Exam questions are loading.')}
                            </div>
                        ) : (exam.questions || []).map((question: any, index: number) => (
                            <div key={question.id} className="rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900/70 p-4">
                                <p className="text-base font-semibold text-gray-900 dark:text-white mb-4">{index + 1}. {t(question.question)}</p>
                                <div className="space-y-3">
                                    {(question.options || []).map((option: any) => (
                                        <label key={option.id} className={`flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-colors ${answers[question.id] === option.id ? 'border-brand-300 dark:border-brand-700 bg-brand-50 dark:bg-brand-900/20' : 'border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-950/70 hover:border-brand-200 dark:hover:border-brand-800'}`}>
                                            <input type="radio" name={`question-${question.id}`} checked={answers[question.id] === option.id} onChange={() => setAnswers((current) => ({ ...current, [question.id]: option.id }))} />
                                            <span className="text-sm text-gray-800 dark:text-gray-200">{t(option.option_text)}</span>
                                        </label>
                                    ))}
                                </div>
                            </div>
                        ))}
                        {totalQuestions > 0 && !isExamFullyAnswered && (
                            <p className="text-sm text-amber-700 dark:text-amber-300">
                                {__('All questions must be answered before submitting.')} ({answeredQuestions}/{totalQuestions})
                            </p>
                        )}
                        <button type="button" onClick={submitExam} disabled={actionBusy === 'submitExam' || (totalQuestions > 0 && !isExamFullyAnswered)} className="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 text-white font-medium disabled:opacity-60">
                            {actionBusy === 'submitExam' ? <Loader2 className="w-4 h-4 animate-spin" /> : <ShieldCheck className="w-4 h-4" />}
                            {__('Submit Exam')}
                        </button>
                    </div>
                )}
            </section>
        );
    };

    if (!user) return <Navigate to="/login" replace />;
    if (loading && !course) return <div className="min-h-screen flex items-center justify-center bg-[#f8fafc] dark:bg-gray-950"><Loader2 className="w-10 h-10 text-brand-600 animate-spin" /></div>;
    if (!course || error) return <div className="min-h-screen bg-[#f8fafc] dark:bg-gray-950 py-16"><div className="max-w-3xl mx-auto px-4 text-center"><h1 className="text-2xl font-bold text-gray-900 dark:text-white mb-3">{__('Learning course unavailable')}</h1><p className="text-gray-500 dark:text-gray-400 mb-6">{error || __('This course could not be loaded for learning access.')}</p><AppLink to="/dashboard?tab=courses" className="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-brand-600 text-white font-medium"><ArrowLeft className="w-4 h-4" />{__('Back to My Courses')}</AppLink></div></div>;

    return (
        <div className="min-h-screen bg-[#f8fafc] dark:bg-[radial-gradient(circle_at_top,_rgba(220,38,38,0.18),_transparent_20%),linear-gradient(180deg,#0b1120_0%,#111827_100%)]">
            <div className="max-w-[1500px] mx-auto px-4 sm:px-5 lg:px-6 py-6">
                <div className="flex flex-col xl:flex-row gap-4">
                    <aside className="xl:w-[330px] shrink-0">
                        <div className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-[#121a2b]/95 backdrop-blur p-4 sticky top-24 shadow-[0_20px_70px_-48px_rgba(15,23,42,0.55)]">
                            <AppLink to="/dashboard?tab=courses" className="inline-flex items-center gap-2 text-sm font-medium text-brand-600 mb-4">
                                <ArrowLeft className="w-4 h-4" />
                                {__('Back to My Courses')}
                            </AppLink>
                            <h1 className="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{t(course.title)}</h1>
                            <p className="text-sm text-gray-500 dark:text-gray-400 mt-2">{t(course.short_description)}</p>

                            <div className="mt-4 rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50 p-3.5">
                                <div className="flex items-center justify-between text-sm font-medium text-gray-600 dark:text-gray-300 mb-2">
                                    <span>{__('Course Progress')}</span>
                                    <span>{course.progress?.percent ?? 0}%</span>
                                </div>
                                <div className="h-2 rounded-full bg-gray-200 dark:bg-gray-800 overflow-hidden">
                                    <div className="h-full rounded-full bg-gradient-to-r from-brand-500 via-brand-600 to-emerald-500" style={{ width: `${course.progress?.percent ?? 0}%` }} />
                                </div>
                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-3">
                                    {course.progress?.completed_items ?? 0} / {course.progress?.total_items ?? 0} {__('items completed')}
                                </p>
                            </div>

                            {(course.certificate?.available || course.certificate?.status === 'locked_real_name') && (
                                <div className="mt-3 rounded-xl border border-amber-100 dark:border-amber-900/40 bg-amber-50/80 dark:bg-amber-900/20 p-3.5">
                                    <div className="flex items-start gap-3">
                                        <div className="w-10 h-10 rounded-xl bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center text-amber-700 dark:text-amber-300 shrink-0">
                                            <Award className="w-5 h-5" />
                                        </div>
                                        <div className="min-w-0">
                                            <p className="font-semibold text-amber-800 dark:text-amber-200">
                                                {course.certificate?.available ? __('Certificate Ready') : __('Certificate Name Required')}
                                            </p>
                                            <p className="text-xs text-amber-700/90 dark:text-amber-300/90 mt-1">
                                                {course.certificate?.locked_reason || __('Download your course certificate with your account name once you finish successfully.')}
                                            </p>
                                            {course.certificate?.download_url ? (
                                                <a href={course.certificate.download_url} className="inline-flex items-center gap-2 text-sm font-semibold text-amber-800 dark:text-amber-200 mt-3">
                                                    <Award className="w-4 h-4" />
                                                    {__('Download Certificate')}
                                                </a>
                                            ) : course.certificate?.status === 'locked_real_name' ? (
                                                <AppLink to="/dashboard?tab=profile" className="inline-flex items-center gap-2 text-sm font-semibold text-amber-800 dark:text-amber-200 mt-3">
                                                    <User className="w-4 h-4" />
                                                    {__('Set Real Name')}
                                                </AppLink>
                                            ) : null}
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="mt-5 space-y-3 max-h-[72vh] overflow-y-auto pe-1 app-scrollbar-soft">
                                {(course.modules || []).map((module: any) => (
                                    <div key={module.id} className="rounded-xl border border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/40 p-3.5">
                                        <button
                                            type="button"
                                            onClick={() => setExpandedModules((current) => ({ ...current, [module.id]: !current[module.id] }))}
                                            className="w-full flex items-start justify-between gap-3 text-start"
                                        >
                                            <div className="min-w-0">
                                                <p className="text-sm font-semibold text-gray-900 dark:text-white">{t(module.title)}</p>
                                                <p className="text-xs text-gray-500 dark:text-gray-400 mt-1">{module.lessons_count} {__('Lessons')}</p>
                                            </div>
                                            <div className="flex items-center gap-2 shrink-0">
                                                {module.is_unlocked ? <CheckCircle2 className={`w-5 h-5 ${module.is_completed ? 'text-emerald-500' : 'text-brand-500'}`} /> : <Lock className="w-5 h-5 text-gray-400" />}
                                                {expandedModules[module.id] ? <ChevronUp className="w-4 h-4 text-gray-400" /> : <ChevronDown className="w-4 h-4 text-gray-400" />}
                                            </div>
                                        </button>

                                        {expandedModules[module.id] && (
                                            <div className="space-y-1.5 mt-3">
                                                {(module.lessons || []).map((lesson: any) => (
                                                    <button key={lesson.id} type="button" onClick={() => lesson.is_unlocked && selectTarget({ type: 'lesson', id: lesson.id })} disabled={!lesson.is_unlocked} className={`w-full flex items-center gap-3 p-2.5 rounded-xl text-start transition-all ${(target?.type === 'lesson' && target.id === lesson.id) ? 'bg-brand-50 dark:bg-brand-900/20 border border-brand-200 dark:border-brand-800 shadow-sm' : 'bg-white dark:bg-gray-900 border border-transparent'} ${lesson.is_unlocked ? 'hover:border-brand-200 dark:hover:border-brand-800' : 'opacity-60 cursor-not-allowed'}`}>
                                                        {lesson.is_unlocked ? (lesson.is_completed ? <CheckCircle2 className="w-4 h-4 text-emerald-500 shrink-0" /> : <PlayCircle className="w-4 h-4 text-brand-500 shrink-0" />) : <Lock className="w-4 h-4 text-gray-400 shrink-0" />}
                                                        <div className="min-w-0 flex-1">
                                                            <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{t(lesson.title)}</p>
                                                            <p className="text-xs text-gray-500 dark:text-gray-400">{lesson.duration_minutes} {__('Minutes')}</p>
                                                        </div>
                                                    </button>
                                                ))}

                                                {module.exam && (
                                                    <button type="button" onClick={() => module.exam.is_unlocked && selectTarget({ type: 'exam', id: module.exam.id })} disabled={!module.exam.is_unlocked} className={`w-full flex items-center gap-3 p-2.5 rounded-xl text-start transition-all ${(target?.type === 'exam' && target.id === module.exam.id) ? 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 shadow-sm' : 'bg-white dark:bg-gray-900 border border-transparent'} ${module.exam.is_unlocked ? 'hover:border-amber-200 dark:hover:border-amber-800' : 'opacity-60 cursor-not-allowed'}`}>
                                                        {module.exam.is_unlocked ? (module.exam.is_passed ? <ShieldCheck className="w-4 h-4 text-emerald-500 shrink-0" /> : <BookOpen className="w-4 h-4 text-amber-500 shrink-0" />) : <Lock className="w-4 h-4 text-gray-400 shrink-0" />}
                                                        <div className="min-w-0 flex-1">
                                                            <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{t(module.exam.title)}</p>
                                                            <p className="text-xs text-gray-500 dark:text-gray-400">{module.exam.question_count} {__('Questions')}</p>
                                                        </div>
                                                    </button>
                                                )}
                                            </div>
                                        )}
                                    </div>
                                ))}
                            </div>
                        </div>
                    </aside>

                    <main className="flex-1 min-w-0">
                        {lessonState && (
                            <div className="space-y-4">
                                <section className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-[#111827]/95 backdrop-blur p-5 sm:p-6 shadow-[0_22px_80px_-56px_rgba(15,23,42,0.65)]">
                                    <div className="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4 mb-5">
                                        <div>
                                            <div className="inline-flex items-center gap-2 rounded-xl bg-brand-50 dark:bg-brand-900/20 px-3 py-1 text-xs font-semibold text-brand-700 dark:text-brand-300 mb-2">
                                                <PlayCircle className="w-4 h-4" />
                                                {t(lessonState.module.title)}
                                            </div>
                                            <h2 className="text-2xl sm:text-4xl font-bold text-gray-900 dark:text-white leading-tight">{t(lessonState.lesson.title)}</h2>
                                            {lessonState.lesson.description && <p className="text-sm sm:text-base text-gray-500 dark:text-gray-400 mt-3 max-w-3xl">{t(lessonState.lesson.description)}</p>}
                                        </div>

                                        <div className="flex flex-wrap gap-3">
                                            <div className="inline-flex items-center gap-2 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-800 px-4 py-2.5 text-sm font-medium text-gray-700 dark:text-gray-300">
                                                <Clock className="w-4 h-4 text-brand-500" />
                                                {lessonState.lesson.duration_minutes} {__('Minutes')}
                                            </div>
                                            <button type="button" onClick={completeLesson} disabled={actionBusy === 'lesson' || lessonState.lesson.is_completed} className="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-brand-700 text-white font-medium disabled:opacity-60">
                                                {actionBusy === 'lesson' ? <Loader2 className="w-4 h-4 animate-spin" /> : <CheckCircle2 className="w-4 h-4" />}
                                                {lessonState.lesson.is_completed ? __('Completed') : __('Mark as completed')}
                                            </button>
                                        </div>
                                    </div>

                                    <div className="rounded-2xl overflow-hidden bg-gray-950 border border-gray-900/60 shadow-[0_18px_48px_-36px_rgba(0,0,0,0.75)]">
                                        {lessonState.lesson.video?.source_type === 'upload' && <video ref={videoRef} controls className="w-full aspect-video" src={lessonState.lesson.video.url} onPause={syncVideo} onEnded={syncVideo} />}
                                        {lessonState.lesson.video?.source_type === 'embed' && <iframe src={lessonState.lesson.video.embed_url} className="w-full aspect-video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowFullScreen title={t(lessonState.lesson.title)} />}
                                        {!lessonState.lesson.video && <div className="aspect-video flex items-center justify-center text-gray-300"><BookOpen className="w-10 h-10" /></div>}
                                    </div>
                                </section>

                                {lessonState.lesson.documents?.length > 0 && (
                                    <section className="rounded-2xl border border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-[#111827]/95 p-4 sm:p-5">
                                        <div className="flex items-center justify-between gap-3 mb-3">
                                            <div>
                                                <h3 className="text-lg font-semibold text-gray-900 dark:text-white">{__('Lesson Documents')}</h3>
                                                <p className="text-sm text-gray-500 dark:text-gray-400">{__('Open lesson files and references in a separate tab.')}</p>
                                            </div>
                                        </div>
                                        <div className="grid grid-cols-1 lg:grid-cols-2 gap-3">
                                            {lessonState.lesson.documents.map((document: any) => (
                                                <a key={document.id} href={document.url} target="_blank" rel="noreferrer" className="flex items-center justify-between gap-4 p-3.5 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-800 hover:border-brand-200 dark:hover:border-brand-800 transition-colors">
                                                    <div className="flex items-center gap-3 min-w-0">
                                                        <FileText className="w-5 h-5 text-brand-500 shrink-0" />
                                                        <div className="min-w-0">
                                                            <p className="text-sm font-medium text-gray-900 dark:text-white truncate">{document.name || document.file_name}</p>
                                                            <p className="text-xs text-gray-500 dark:text-gray-400">{document.mime_type || __('Document')}</p>
                                                        </div>
                                                    </div>
                                                    <span className="text-sm font-medium text-brand-600">{__('Open')}</span>
                                                </a>
                                            ))}
                                        </div>
                                    </section>
                                )}

                                {relatedLessonExamState && renderExamPanel(relatedLessonExamState, 'inline')}
                            </div>
                        )}

                        {!lessonState && standaloneExamState && renderExamPanel(standaloneExamState, 'standalone')}
                    </main>
                </div>
            </div>
        </div>
    );
};

export default LearnCoursePage;
