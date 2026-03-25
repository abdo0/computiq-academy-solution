import React from 'react';
import { Facebook, Twitter, Instagram, Linkedin, Mail, Phone, MapPin, ArrowUp } from 'lucide-react';
import { useLanguage } from '../contexts/LanguageContext';
import { useSettings } from '../contexts/SettingsContext';
import { useAppNavigate } from '../hooks/useAppNavigate';
import { useTheme } from '../contexts/ThemeContext';
import Logo from './Logo';

const Footer: React.FC = () => {
  const { language } = useLanguage();
  const { settings } = useSettings();
  const { theme } = useTheme();
  const navigate = useAppNavigate();

  const handleLinkClick = (e: React.MouseEvent, path: string) => {
    e.preventDefault();
    navigate(path);
    window.scrollTo(0, 0);
  };

  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <footer className="bg-[#fcfdfd] dark:bg-gray-900 pt-16 border-t border-gray-100 dark:border-gray-800">
      <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8">
        
        {/* Main 4 Columns Grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
          
          {/* Column 1: Educational Platform */}
          <div>
            <h4 className="text-lg font-black text-brand-600 dark:text-brand-400 mb-6">
              المنصة التعليمية
            </h4>
            <ul className="space-y-4">
              <li><button onClick={(e) => handleLinkClick(e, '/courses')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">الدورات</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/blog')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">المدونة</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/about')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">من نحن</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/contact')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">الدعم الفني</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/page/terms-of-service')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">شروط واحكام</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/page/privacy-policy')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">سياسة الخصوصية</button></li>
            </ul>
          </div>

          {/* Column 2: Business */}
          <div>
            <h4 className="text-lg font-black text-brand-600 dark:text-brand-400 mb-6">
              Computiq للأعمال
            </h4>
            <ul className="space-y-4">
              <li><button onClick={(e) => handleLinkClick(e, '/')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">خدمات الشركات</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">توظيف الخريجين</button></li>
              <li><button onClick={(e) => handleLinkClick(e, '/')} className="text-sm font-bold text-gray-600 dark:text-gray-400 hover:text-brand-600 transition-colors">شركاء النجاح</button></li>
            </ul>
          </div>

          {/* Column 3: Contact */}
          <div>
            <h4 className="text-lg font-black text-brand-600 dark:text-brand-400 mb-6">
              ابقي على تواصل
            </h4>
            <ul className="space-y-4">
              <li className="flex items-center gap-3">
                <Mail className="w-5 h-5 text-gray-400" />
                <span className="text-sm font-bold text-gray-600 dark:text-gray-400 dir-ltr text-end">{settings.contactEmail || 'info@computiq.tech'}</span>
              </li>
              <li className="flex items-center gap-3">
                <Phone className="w-5 h-5 text-gray-400" />
                <span className="text-sm font-bold text-gray-600 dark:text-gray-400 dir-ltr text-end">00201012345678</span>
              </li>
            </ul>
          </div>

          {/* Column 4: Social & Logo */}
          <div className="flex flex-col">
            <h4 className="text-lg font-black text-brand-600 dark:text-brand-400 mb-6">
              تابعنا على منصات التواصل الاجتماعي
            </h4>
            
            <div className="flex gap-4 mb-10">
              <a href="#" className="w-10 h-10 border-2 border-brand-100 dark:border-brand-900 rounded-full flex items-center justify-center text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/50 transition-colors">
                 <Facebook size={18} />
              </a>
              <a href="#" className="w-10 h-10 border-2 border-brand-100 dark:border-brand-900 rounded-full flex items-center justify-center text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/50 transition-colors">
                 <Twitter size={18} />
              </a>
              <a href="#" className="w-10 h-10 border-2 border-brand-100 dark:border-brand-900 rounded-full flex items-center justify-center text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/50 transition-colors">
                 <Linkedin size={18} />
              </a>
              <a href="#" className="w-10 h-10 border-2 border-brand-100 dark:border-brand-900 rounded-full flex items-center justify-center text-brand-600 dark:text-brand-400 hover:bg-brand-50 dark:hover:bg-brand-900/50 transition-colors">
                 <Instagram size={18} />
              </a>
            </div>

            <div className="mt-auto">
              <Logo 
                imageClassName="h-10 w-auto object-contain mx-0.5"
                textClassName="text-[26px] font-black tracking-tight text-gray-900 dark:text-white opacity-90"
              />
            </div>
          </div>

        </div>
      </div>

      {/* Bottom Bar */}
      <div className="border-t border-gray-100 dark:border-gray-800">
        <div className="max-w-screen-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
           <p className="text-xs font-bold text-gray-500">
             جميع الحقوق محفوظة منصة Computiq © {new Date().getFullYear()}
           </p>
           
           <div className="flex items-center gap-6">
              <button onClick={(e) => handleLinkClick(e, '/page/privacy-policy')} className="text-xs font-bold text-gray-500 hover:text-brand-600 transition-colors">سياسة الخصوصية</button>
              <button onClick={(e) => handleLinkClick(e, '/page/terms-of-service')} className="text-xs font-bold text-gray-500 hover:text-brand-600 transition-colors">الشروط والأحكام</button>
           </div>
        </div>
      </div>
    </footer>
  );
};

export default Footer;