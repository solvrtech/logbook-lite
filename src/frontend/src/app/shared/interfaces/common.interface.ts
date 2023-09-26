import { MailSetting } from 'src/app/apps/interfaces/app.interface';

/**
 * Can be used as a page's current state.
 */
export type PageState = 'loading' | 'loaded' | 'error' | 'unauthorized' | 'restricted_access';

/**
 * Common properties for a form-based page, e.g. on a edit or detail page.
 */
export interface FormPageProps {
  state: PageState;
  message?: string;
  returnUrl?: string;
}

/**
 * Properties for a select option in a form.
 */
export interface FormDropdown {
  value: any;
  description: string;
  icon?: string;
  iconStyle?: any;
}

export type ViewMode = 'create' | 'edit' | 'detail';

export type PageMode = 'LOADING' | 'READY' | 'INVALID' | 'FINISHED';

/**
 * Common properties for a breadcrumb in a page.
 */
export interface BreadCrumb {
  url: string;
  label: string;
}

export interface Language {
  applicationSubtitle: string;
  // Default language and locale if not already selected
  defaultLanguage: string;
  // list of available languages
  languagePreferences: [];
}

export interface AutoRefresh {
  enable: boolean;
  interval: number;
  enMessage: boolean;
  inMessage: number;
}

export interface CheckMailConnection {
  mailSetting: MailSetting;
  appId: string;
}

/** Supported application types */
export const APP_TYPE_LARAVEL = 'laravel';
export const APP_TYPE_SYMFONY = 'symfony';
export const APP_TYPE_WORDPRESS = 'wordpress';
export const APP_TYPE_GENERAL = 'general';
