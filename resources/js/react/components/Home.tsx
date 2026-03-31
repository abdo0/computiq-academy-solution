import React, { useState, useEffect } from 'react';
import Hero from './Hero';
import Sponsors from './home/Sponsors';
import FeaturesBand from './home/FeaturesBand'; // Actually CategoryCards visually
import CourseGrid from './home/CourseGrid';
import BusinessBanner from './home/BusinessBanner';
import InstructorBanner from './home/InstructorBanner';
import { Stat } from '../types';
import { dataService } from '../services/dataService';
import { useLanguage } from '../contexts/LanguageContext';
import { useTranslation } from '../contexts/TranslationProvider';
import { useCurrentRouteBootstrap } from '../contexts/RouteBootstrapContext';

const Home: React.FC = () => {
  const { language } = useLanguage();
  const { __, t } = useTranslation();
  const initialBootstrap = useCurrentRouteBootstrap<any>();
  const initialHomeData = initialBootstrap?.homeData;

  const [stats, setStats] = useState<Stat[]>(() => initialHomeData?.stats || []);
  const [sections, setSections] = useState<Record<string, any>>(() => initialHomeData?.sections || {});
  const [courses, setCourses] = useState<any[]>(() => initialHomeData?.courses || []);
  const [courseCategories, setCourseCategories] = useState<any[]>(() => initialHomeData?.course_categories || []);
  const [sponsors, setSponsors] = useState<{ partners: any[], employment: any[] }>(() => initialHomeData?.sponsors || { partners: [], employment: [] });
  const [isLoading, setIsLoading] = useState(() => !initialHomeData);

  useEffect(() => {
    if (initialHomeData) {
      setStats(initialHomeData.stats || []);
      setSections(initialHomeData.sections || {});
      setCourses(initialHomeData.courses || []);
      setCourseCategories(initialHomeData.course_categories || []);
      setSponsors(initialHomeData.sponsors || { partners: [], employment: [] });
      setIsLoading(false);
      return;
    }

    const fetchData = async () => {
      setIsLoading(true);
      try {
        let homeData = null;

        const rootElement = document.getElementById('root');
        if (rootElement && !homeData) {
          const initialDataAttr = rootElement.getAttribute('data-initial');
          if (initialDataAttr && initialDataAttr.trim() !== '') {
            try {
              const initialData = JSON.parse(initialDataAttr);
              if (initialData.homeData) {
                homeData = initialData.homeData;
                delete initialData.homeData;
                rootElement.setAttribute('data-initial', JSON.stringify(initialData));
              }
            } catch (error) {
              console.error('Failed to parse initial home data:', error);
            }
          }
        }

        if (!homeData) {
          homeData = await dataService.getHomeData();
        }

        setStats(homeData.stats || []);
        setSections(homeData.sections || {});
        setCourses(homeData.courses || []);
        setCourseCategories(homeData.course_categories || []);
        setSponsors(homeData.sponsors || { partners: [], employment: [] });
      } catch (error) {
        console.error('Failed to load home data', error);
      } finally {
        setIsLoading(false);
      }
    };
    fetchData();
  }, [initialHomeData, language]);

  // Helper: filter courses by category slug
  const getCoursesByCategory = (slug: string) =>
    courses.filter((c: any) => c.category_slug === slug);



  // Map courses to CourseCard format
  const mapCourseToCard = (c: any) => ({
    image: c.image,
    badge: c.is_best_seller ? __('Best seller') : c.is_live ? __('Live') : __('Recorded course'),
    badgeColor: c.is_best_seller ? 'bg-amber-500' : c.is_live ? 'bg-indigo-600' : 'bg-emerald-500',
    title: t(c.title),
    instructor: t(c.instructor_name),
    instructorImage: c.instructor_image,
    instructorSlug: c.instructor_slug,
    rating: c.rating,
    reviewCount: c.review_count,
    hours: c.duration_hours,
    students: c.students_count,
    price: String(c.price),
    oldPrice: c.old_price ? String(c.old_price) : undefined,
    link: `/courses/${c.slug}`,
    courseId: c.id,
    categorySlug: c.category_slug,
  });

  const allMappedCourses = courses.map(mapCourseToCard);

  // Build tabs from categories for main grid
  const categoryTabs = [
    { label: { ar: 'كل التخصصات', en: 'All Categories', ku: 'هەموو' }, slug: 'all' },
    ...courseCategories.map((cat: any) => ({ label: cat.name, slug: cat.slug })),
  ];

  return (
    <>
      <Hero sectionData={sections.home_hero_extra} stats={stats} isLoading={isLoading} />

      {/* Course Grid 1 - Main */}
      <CourseGrid
        sectionData={sections.home_main_courses}
        isLoading={isLoading}
        showTabs={true}
        tabs={categoryTabs}
        courses={allMappedCourses.length > 0 ? allMappedCourses : undefined}
      />

      {/* Category Cards */}
      <FeaturesBand sectionData={sections.home_category_cards} isLoading={isLoading} />

      {/* Develop Your Team (Business) */}
      <BusinessBanner sectionData={sections.home_business_banner} />

      {/* Partners and Employment */}
      <Sponsors sponsorsData={sponsors} isLoading={isLoading} />

      {/* Be an Instructor */}
      <InstructorBanner sectionData={sections.home_instructor_banner} />
    </>
  );
};

export default Home;
