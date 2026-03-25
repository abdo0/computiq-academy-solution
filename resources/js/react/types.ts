import React from 'react';

export interface Stat {
  id: number;
  label: string;
  value: string;
  icon?: React.ReactNode;
  iconName?: string; // For serializing icon name from API
}

export interface Category {
  id: string;
  name: string;
  slug: string;
}

export interface ChatMessage {
  id: string;
  role: 'user' | 'model';
  text: string;
  timestamp: Date;
}

export interface BlogPost {
  id: string;
  slug: string;
  title: string;
  excerpt: string;
  content: string;
  categoryId: string | null;
  category: string;
  authorId: string | null;
  authorName: string;
  featuredImageUrl: string | null;
  contentImageUrl?: string | null;
  publishedAt: string | null;
  isPublished: boolean;
  createdAt: string;
  updatedAt: string;
  // Legacy fields for backward compatibility
  imageUrl?: string;
  date?: string;
  author?: string;
}

export interface FaqItem {
  id: string;
  question: string;
  answer: string;
}

export interface Testimonial {
  id: string;
  name: string;
  comment: string;
  rating: number;
}

export interface User {
  id: string;
  name: string;
  email: string;
  isVerified: boolean;
  avatar?: string;
}

// Settings and Dynamic Content Interfaces

export interface LocalizedString {
  ar: string;
  en: string;
  ku: string;
}

export interface SeoConfig {
  title: string;
  description: string;
  keywords: string;
  ogImage?: string;
}

export interface AppSettings {
  logoUrl?: string;
  siteName: string;
  currency: {
    code: string;
    symbol: string;
  };
  contactEmail: string;
  contactPhone: string[];
  address: string;
  socialLinks: {
    twitter: string;
    facebook: string;
    instagram: string;
    youtube?: string;
  };
  footerDesc: string;
  defaultSeo: SeoConfig;
  refundMaxDays?: number;
  footerPages?: string[];
  otherPages?: Array<{ slug: string; title: LocalizedString | string }>;
}

export interface HeroContent {
  title: string;
  subtitle: string;
  cta_text?: string;
  perks?: string;
  background_image?: string;
}

export interface HomeSection {
  title: string | LocalizedString;
  description: string | LocalizedString | null;
  extra_data: Record<string, any> | null;
}

export interface ApiResponse<T> {
  data: T;
  status: number;
  message: string;
  meta?: {
    seo?: SeoConfig;
    pagination?: {
      total: number;
      page: number;
      limit: number;
    }
  };
}