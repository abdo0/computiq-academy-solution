import React from 'react';
import { useSettings } from '../contexts/SettingsContext';
import { useLanguage } from '../contexts/LanguageContext';

interface LogoProps {
  className?: string;
  textClassName?: string;
  imageClassName?: string;
}

const Logo: React.FC<LogoProps> = ({
  className = '',
  textClassName = 'text-xl font-black text-gray-900 dark:text-white',
  imageClassName = 'h-8 w-auto'
}) => {
  const { language } = useLanguage();

  return (
    <div className={`flex items-center gap-1 ${className}`} dir="ltr">
      <span className={textClassName}>COMPU</span>
      <img
        src="/images/SVG/mini-logo.svg"
        alt="Computiq Logo"
        className={imageClassName}
      />
    </div>
  );
};

export default Logo;
