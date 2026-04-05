import React from 'react';
import { Star, Clock, User, ShoppingCart, CheckCircle, Loader2 } from 'lucide-react';
import { useAppNavigate } from '../../hooks/useAppNavigate';
import { useTranslation } from '../../contexts/TranslationProvider';
import { useCart } from '../../contexts/CartContext';
import AppLink from '../common/AppLink';
import { useCurrency } from '../../utils/currency';

interface CourseCardProps {
  image: string;
  badge?: string;
  badgeColor?: string;
  deliveryType?: string;
  title: string;
  instructor: string;
  instructorImage?: string;
  instructorSlug?: string;
  rating: number;
  reviewCount?: number;
  hours: number;
  students: number;
  price: string;
  oldPrice?: string;
  link: string;
  courseId?: number;
}

const CourseCard: React.FC<CourseCardProps> = ({
  image,
  badge,
  badgeColor = 'bg-brand-600',
  deliveryType,
  title,
  instructor,
  instructorImage,
  instructorSlug,
  rating,
  reviewCount,
  hours,
  price,
  oldPrice,
  link,
  courseId,
}) => {
  const navigate = useAppNavigate();
  const { __ } = useTranslation();
  const { addToCart, isInCart } = useCart();
  const { formatAmount } = useCurrency();
  const [isAdding, setIsAdding] = React.useState(false);
  const inCart = courseId ? isInCart(courseId) : false;
  const deliveryTypeLabel = deliveryType === 'onsite'
    ? __('On-site')
    : deliveryType === 'hybrid'
      ? __('Hybrid')
      : __('Online');
  const deliveryTypeClassName = deliveryType === 'onsite'
    ? 'bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-300'
    : deliveryType === 'hybrid'
      ? 'bg-sky-50 dark:bg-sky-900/20 text-sky-700 dark:text-sky-300'
      : 'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400';

  const handleAddToCart = async (e: React.MouseEvent) => {
    e.stopPropagation();
    e.preventDefault();
    if (courseId && !inCart && !isAdding) {
      setIsAdding(true);
      await addToCart(courseId);
      setIsAdding(false);
    }
  };

  return (
    <div className="group bg-white dark:bg-slate-900 rounded-md overflow-hidden border border-gray-100 dark:border-slate-800/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.1)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] dark:hover:shadow-[0_20px_40px_rgb(0,0,0,0.2)] hover:-translate-y-1 transition-all duration-500 flex flex-col h-full relative">
      <AppLink to={link} className="absolute inset-0 z-10" aria-label={title}></AppLink>
      
      {/* Image Container */}
      <div className="relative overflow-hidden aspect-[16/10] rounded-t-md">
        <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-black/0 to-black/10 z-10"></div>
        <img
          src={image}
          alt={title}
          className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 rounded-md"
          loading="lazy"
        />
        {/* Top Right Badge */}
        {badge && (
          <span className={`absolute top-4 start-4 z-20 ${badgeColor} text-white text-xs font-bold px-3 py-1.5 rounded-xl shadow-lg backdrop-blur-sm`}>
            {badge}
          </span>
        )}
        
        {/* Rating Floating Badge (Bottom Right) */}
        <div className="absolute bottom-4 end-4 z-20 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md px-2.5 py-1.5 rounded-xl flex items-center gap-1.5 shadow-lg">
           <Star className="w-3.5 h-3.5 text-amber-500 fill-amber-500" />
           <span className="font-bold text-xs text-gray-900 dark:text-white">{rating.toFixed(1)}</span>
           <span className="text-[10px] text-gray-500 dark:text-gray-400 font-medium">({reviewCount})</span>
        </div>
      </div>

      {/* Content Area */}
      <div className="p-6 flex-1 flex flex-col relative">
        
        {/* Instructor Avatar (Floating Overlapping) */}
        <AppLink 
          to={instructorSlug ? `/instructors/${instructorSlug}` : '#'}
          className="absolute -top-6 start-6 z-20 flex items-center gap-3 cursor-pointer group/instructor"
        >
          {instructorImage ? (
            <img 
              src={instructorImage} 
              alt={instructor} 
              className="w-12 h-12 rounded-full object-cover ring-4 ring-white dark:ring-slate-900 shadow-md bg-white group-hover/instructor:ring-brand-100 transition-colors" 
            />
          ) : (
            <div className="w-12 h-12 rounded-full bg-brand-50 dark:bg-brand-900/50 flex items-center justify-center text-brand-600 dark:text-brand-400 text-lg font-bold ring-4 ring-white dark:ring-slate-900 shadow-md backdrop-blur-sm group-hover/instructor:ring-brand-100 transition-colors">
              {instructor.charAt(0)}
            </div>
          )}
          <div className="pt-6">
             <p className="text-xs text-gray-500 dark:text-gray-400 font-bold line-clamp-1 group-hover/instructor:text-brand-600 transition-colors">{instructor}</p>
          </div>
        </AppLink>
        
        {/* Course Title */}
        <h3 className="text-base font-bold text-gray-900 dark:text-white mb-4 mt-8 line-clamp-2 leading-snug group-hover:text-brand-600 dark:group-hover:text-brand-400 transition-colors">
          {title}
        </h3>

        {/* Feature Pills */}
        <div className="flex flex-wrap items-center gap-2 mb-6">
          <span className="flex items-center gap-1.5 px-3 py-1.5 bg-gray-50 dark:bg-slate-800/50 rounded-md text-xs font-medium text-gray-600 dark:text-gray-300">
            <Clock className="w-3.5 h-3.5 text-gray-400" />
            {hours} {__('Hours')}
          </span>
          <span className={`flex items-center gap-1.5 px-3 py-1.5 rounded-md text-xs font-medium ${deliveryTypeClassName}`}>
            <User className="w-3.5 h-3.5" />
            {deliveryTypeLabel}
          </span>
        </div>

        {/* Spacer */}
        <div className="flex-1"></div>

        {/* Bottom Price/CTA Row */}
        <div className="flex items-center justify-between pt-5 mt-2 border-t border-gray-100 dark:border-slate-800 font-metropolis">
          
          {/* Price */}
          <div className="flex flex-col items-start leading-tight">
             {oldPrice && (
               <span className="text-xs text-gray-400 dark:text-gray-500 line-through mb-0.5">{formatAmount(oldPrice)}</span>
             )}
             <div className="flex items-baseline gap-1">
               <span className="text-xl font-black text-brand-600 dark:text-brand-400">
                 {formatAmount(price)}
               </span>
             </div>
          </div>

          {/* Action Buttons */}
          <div className="flex items-center gap-2 relative z-20">
            <button 
              onClick={handleAddToCart}
              disabled={isAdding}
              className={`p-2.5 rounded-[14px] transition-all active:scale-95 ${
                inCart
                  ? 'text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/30'
                  : 'text-gray-500 dark:text-gray-400 bg-gray-50 dark:bg-slate-800 hover:text-brand-600 dark:hover:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/30'
              } ${isAdding ? 'opacity-75 cursor-not-allowed' : ''}`}
              aria-label={inCart ? __('In Cart') : __('Add to Cart')}
            >
              {isAdding ? <Loader2 className="w-[18px] h-[18px] animate-spin" /> : 
               inCart ? <CheckCircle className="w-[18px] h-[18px]" /> : <ShoppingCart className="w-[18px] h-[18px]" />}
            </button>
            <AppLink
              to={link}
              className="flex justify-center flex-1 sm:flex-none items-center bg-brand-600 text-white hover:bg-brand-700 text-sm font-bold px-6 py-2.5 rounded-[14px] transition-all shadow-md shadow-brand-600/20 hover:shadow-lg hover:shadow-brand-600/30 active:scale-95"
            >
              {__('Subscribe now')}
            </AppLink>
          </div>

        </div>
      </div>
    </div>
  );
};

export default CourseCard;
