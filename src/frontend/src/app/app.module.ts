import { APP_INITIALIZER, Injector, LOCALE_ID, NgModule } from '@angular/core';
import { BrowserModule, Title } from '@angular/platform-browser';

import { COMMA, SPACE } from '@angular/cdk/keycodes';
import { registerLocaleData } from '@angular/common';
import { HttpClient, HttpClientModule } from '@angular/common/http';
import localeEn from '@angular/common/locales/en';
import localeIdExtra from '@angular/common/locales/extra/id';
import localeId from '@angular/common/locales/id';
import { MAT_CHIPS_DEFAULT_OPTIONS } from '@angular/material/chips';
import { DateAdapter, MAT_DATE_FORMATS } from '@angular/material/core';
import { MatIconRegistry } from '@angular/material/icon';
import { BrowserAnimationsModule } from '@angular/platform-browser/animations';
import { LoadingBarModule } from '@ngx-loading-bar/core';
import { LoadingBarHttpClientModule } from '@ngx-loading-bar/http-client';
import { TranslateLoader, TranslateModule, TranslateService } from '@ngx-translate/core';
import { HIGHLIGHT_OPTIONS } from 'ngx-highlightjs';
import { ToastrModule } from 'ngx-toastr';
import { SettingService } from './administration/services/settings/setting.service';
import { AppRoutingModule } from './app-routing.module';
import { AppComponent } from './app.component';
import { I18nInitializer, LanguageInitializer, TokenInitializer } from './app.initializer';
import { AuthService } from './login/services/auth/auth.service';
import { CUSTOM_DATE_FORMATS, CustomDatePickerAdapter } from './shared/adapters/date-adapter';
import { getLocale } from './shared/helpers/locale.helper';

registerLocaleData(localeEn, 'en');
registerLocaleData(localeId, 'id-ID', localeIdExtra);

@NgModule({
  declarations: [AppComponent],
  imports: [
    BrowserModule,
    AppRoutingModule,
    BrowserAnimationsModule,
    HttpClientModule,
    ToastrModule.forRoot(),
    TranslateModule.forRoot({
      loader: {
        provide: TranslateLoader,
        useFactory: I18nInitializer,
        deps: [HttpClient],
      },
    }),
    LoadingBarHttpClientModule,
    LoadingBarModule,
  ],
  providers: [
    { provide: APP_INITIALIZER, useFactory: TokenInitializer, multi: true, deps: [AuthService] },
    {
      provide: APP_INITIALIZER,
      useFactory: LanguageInitializer,
      deps: [TranslateService, Injector, DateAdapter, SettingService],
      multi: true,
    },
    { provide: DateAdapter, useClass: CustomDatePickerAdapter },
    { provide: MAT_DATE_FORMATS, useValue: CUSTOM_DATE_FORMATS },
    { provide: LOCALE_ID, useFactory: getLocale, deps: [SettingService] },
    Title,
    {
      provide: MAT_CHIPS_DEFAULT_OPTIONS,
      useValue: {
        separatorKeyCodes: [COMMA, SPACE],
      },
    },
    {
      provide: HIGHLIGHT_OPTIONS,
      useValue: {
        coreLibraryLoader: () => import('highlight.js/lib/core'),
        languages: {
          xml: () => import('highlight.js/lib/languages/xml'),
          typescript: () => import('highlight.js/lib/languages/typescript'),
          scss: () => import('highlight.js/lib/languages/scss'),
        },
      },
    },
  ],
  bootstrap: [AppComponent],
})
export class AppModule {
  constructor(iconRegistry: MatIconRegistry) {
    iconRegistry.setDefaultFontSetClass('material-symbols-outlined');
  }
}
