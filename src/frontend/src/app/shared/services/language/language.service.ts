import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { DateAdapter } from '@angular/material/core';
import { TranslateService } from '@ngx-translate/core';
import { getFromLocalStorage, saveToLocalStorage } from 'src/app/shared/helpers/local-storage.helper';

@Injectable({
  providedIn: 'root',
})
export class LanguageService {
  constructor(
    private translateService: TranslateService,
    private adapter: DateAdapter<any>,
    protected http: HttpClient
  ) {}
  /**
   * Changes from one language to another
   * @param language locale id, language to use
   * @param reload refresh page as it could fix several issues
   */
  public switchLanguage(code: string, reload = true) {
    saveToLocalStorage('lang', code);
    this.translateService.use(code).subscribe({
      next: res => {
        if (res) {
          this.adapter.setLocale(code);
          if (reload && location != null) {
            location.reload();
          }
        }
      },
      error: err => console.error(err),
    });
  }

  public getCurrentLanguageCode(defaultLanguage: string): string {
    const lang = getFromLocalStorage('lang') || defaultLanguage;
    return lang;
  }

  public loadSelectedLanguageToken(defaultLanguage: string): string {
    const lang = getFromLocalStorage('lang');

    const languageToken = lang ? `language.${lang}` : `language.${defaultLanguage}`;

    return languageToken;
  }
}
