export const environment = {
  // production or not
  production: false,

  // subpath of frontend
  baseHref: '',

  // base url api with base path
  apiUrl: '',

  // prefix to be added for every localStorage variables
  localStoragePrefix: 'logbook_',

  // time-to-live (in minutes) for cached time period for dashboard-widgets
  widgetTTL: 10,

  // default bar color for the top http loading bar
  ngxBarColor: '#fafafa',

  // autoRefresh local storage key
  autoRefreshKey: 'autoRefresh',

  // link for health status
  healthStatusLink: 'https://solvrtech.id/logbook/docs/10#iv-connecting-your-apps-to-log-book',
  scoutapmLink: 'https://scoutapm.com/blog/understanding-load-averages',

  // Default language and locale if not already selected
  defaultLanguage: 'en',
  defaultLocale: 'en-US',

  // list of available languages ('en' only, as default, and the only option when lang setting is not set)
  languageCodes: ['en'],

  // code to locale definition
  languageCodeToLocale: {
    en: 'en-US',
    id: 'id-ID',
  },

  // # IMAGE CROPPER #
  imageCropper: {
    // aspect ratio number to be used to force image cropper ratio
    aspectRatio: 1 / 1,

    // Output format (png)
    resultFormat: 'png',

    // allowed MIME type for image cropper (only raster image is allowed)
    mimeType: 'image/jpeg,image/jpg,image/png',

    // minimum width for image cropper
    minWidth: 100,

    // minimum height for image cropper
    minHeight: 100,
  },

  // # LOGO CONFIG #
  appLogo: {
    // max allowed logo size
    maxFileSize: 2097152,
    // allowed MIME type for image logo selection
    mimeType: 'image/jpeg, image/jpg, image/png',
  },

  // User's security settings
  // At least 1 uppercase letter, 1 lowercase letter, and 1 number
  urlRegex: /^https?:\/\/(?:.*\/)?[^\/]+$/,
  userPasswordRegex: /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d).*$/,
  userPasswordMinLength: 8,
  userPasswordMaxLength: 50,

  // API paths
  authenticationPath: 'api/auth/login',
  currentUserPath: 'api/auth/me',
  refreshTokenPath: 'api/auth/refresh',
  logoutPath: 'api/auth/logout',
  authPath: 'api/auth',
  rolesPath: 'api/role',
  usersPath: 'api/user',
  setPasswordPath: 'api/auth/set-password',
  resetPasswordPath: 'api/auth/reset-password',
  appsPath: 'api/app',
  teamsPath: 'api/team',
  logsPath: 'api/log',
  settingsPath: 'api/setting',
  profilePath: 'api/user-profile',
  healthsPath: 'api/health-status',
  alertPath: 'api/alert',
  messagePath: 'api/notification',
};
