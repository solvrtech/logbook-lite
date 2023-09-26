import { Component, Input, OnInit } from '@angular/core';
import { LangChangeEvent, TranslateService } from '@ngx-translate/core';
import { LanguageService } from '../../services/language/language.service';

interface AvailableLanguage {
  code: string;
  label: string;
}

@Component({
  selector: 'app-language-selector',
  templateUrl: './language-selector.component.html',
  styleUrls: ['./language-selector.component.scss'],
})
export class LanguageSelectorComponent implements OnInit {
  @Input() mode: 'list' | 'menu' = 'list';
  @Input() useIcon = false;
  @Input() reload = false;
  @Input() languages!: any;
  @Input() defaultLanguage!: string;

  availableLanguages: AvailableLanguage[] = [];
  selectedLanguage = '';
  selectedLangCode = '';

  constructor(private languageService: LanguageService, private translateService: TranslateService) {}

  ngOnInit(): void {
    this.selectedLanguage = this.languageService.loadSelectedLanguageToken(this.defaultLanguage);
    this.selectedLangCode = this.languageService.getCurrentLanguageCode(this.defaultLanguage);

    this.translateService.onLangChange.subscribe((event: LangChangeEvent) => {
      this.selectedLanguage = `language.${event.lang}`;
      this.selectedLangCode = event.lang;
    });
  }

  /**
   * Changes from one language to another
   * @param language locale id of the language to use
   */
  public switchLanguage($event: any, language: string) {
    $event.preventDefault();
    this.languageService.switchLanguage(language, this.reload);
  }
}
