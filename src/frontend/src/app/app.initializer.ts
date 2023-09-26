import { LOCATION_INITIALIZED } from '@angular/common';
import { HttpClient } from '@angular/common/http';
import { Injector } from '@angular/core';
import { DateAdapter } from '@angular/material/core';
import { TranslateService } from '@ngx-translate/core';
import { TranslateHttpLoader } from '@ngx-translate/http-loader';
import { environment } from 'src/environments/environment';
import { SettingService } from './administration/services/settings/setting.service';
import { AuthService } from './login/services/auth/auth.service';
import { getCurrentLangApp } from './shared/helpers/locale.helper';

/**
 * Initialize translation sources upon app init
 */
export function I18nInitializer(http: HttpClient): TranslateHttpLoader {
  return new TranslateHttpLoader(http, '' + '/assets/i18n/');
}

/**
 * Initialize language upon app init
 */
export function LanguageInitializer(
  translate: TranslateService,
  injector: Injector,
  dateAdapter: DateAdapter<Date>,
  language: SettingService
) {
  return () =>
    new Promise<any>((resolve: any) => {
      // set language upon app init
      const locationInitialized = injector.get(LOCATION_INITIALIZED, Promise.resolve(null));
      locationInitialized.then(() => {
        language
          .getSettingsAll()
          .toPromise()
          .then(res => {
            if (res?.data) {
              const defaultLang = getCurrentLangApp(
                res.data.generalSetting ? res.data.generalSetting?.defaultLanguage : 'en'
              );

              translate.setDefaultLang(defaultLang);
              translate.use(defaultLang).subscribe({
                next: () => {
                  if (!environment.production) {
                    resolve(defaultLang);
                  }
                },
                error: () => {
                  console.error(`Problem with '${defaultLang}' language initialization.'`);
                },
                complete: () => {
                  resolve(null);
                },
              });
              dateAdapter.setLocale(defaultLang);
            }
          });
      });
    });
}

/**
 * Refresh access token upon app init since JS will not be able to read HttpOnly cookie.
 * Additionally load authenticated user data afterwards.
 */
export function TokenInitializer(authService: AuthService) {
  return () =>
    new Promise((resolve: any) => {
      authService
        .refreshToken()
        .toPromise()
        .then(() => {
          if (authService.currentUser != null) {
            resolve(true);
          } else {
            resolve(true);
          }
        })
        .catch(err => {
          resolve(null);
        });
    });
}
