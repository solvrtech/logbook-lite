import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { environment } from 'src/environments/environment';
import { getFromLocalStorage } from './local-storage.helper';

export function getLocaleForLanguageCode(code: string) {
  if (code === 'en') {
    return 'en-US';
  } else if ((<any>environment.languageCodeToLocale)[code]) {
    return (<any>environment.languageCodeToLocale)[code];
  }
  return environment.defaultLocale;
}

export function getCurrentLocale(settingService: SettingService): string {
  const code = getCurrentLanguageCode(settingService);
  return getLocaleForLanguageCode(code);
}

export function getCurrentLanguageCode(settingService: SettingService): string {
  if (settingService?.currentLanguage) {
    const code = getFromLocalStorage('lang') || settingService.currentLanguage?.defaultLanguage;
    return code;
  } else {
    const code = getFromLocalStorage('lang') || environment.defaultLanguage;
    return code;
  }
}

export function getCurrentLangApp(lang: string): string {
  if (lang) {
    const code = getFromLocalStorage('lang') || lang;
    return code;
  } else {
    const code = getFromLocalStorage('lang') || environment.defaultLanguage;
    return code;
  }
}

export function getLocale(settingService: SettingService) {
  let code = '';
  settingService.getSettingsAll().subscribe({
    next: res => {
      if (res?.data.generalSetting != null) {
        code = getFromLocalStorage('lang') || res.data.generalSetting?.defaultLanguage;
        return getLocaleForLanguageCode(code);
      } else {
        code = getFromLocalStorage('lang') || environment.defaultLanguage;
        return getLocaleForLanguageCode(code);
      }
    },
    error: err => {
      console.log(err);
      code = getFromLocalStorage('lang') || environment.defaultLanguage;
      return getLocaleForLanguageCode(code);
    },
  });
}
