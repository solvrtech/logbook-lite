import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { AlertsService } from 'src/app/shared/services/alerts/alerts.service';
import { LanguageService } from 'src/app/shared/services/language/language.service';
import { environment } from 'src/environments/environment';
import { SettingService } from '../../services/settings/setting.service';

@Component({
  selector: 'app-general-settings',
  templateUrl: './general-settings.component.html',
  styleUrls: ['./general-settings.component.scss'],
})
export class GeneralSettingsComponent extends BaseComponent implements OnInit {
  formGroup!: FormGroup;
  languages: FormDropdown[] = [
    { value: 'en', description: 'language.en' },
    { value: 'id', description: 'language.id' },
  ];
  defaults: FormDropdown[] = [];
  defaultLanguage = environment.defaultLanguage;
  languageCodes = environment.languageCodes;

  constructor(
    private fb: FormBuilder,
    private settingService: SettingService,
    private alerts: AlertsService,
    private router: Router,
    private languageService: LanguageService
  ) {
    super();
    this.initFormGroup();
  }

  ngOnInit(): void {
    this.fetchModel();
  }

  /**
   * Setup form controls and initial validators
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      appSubtitle: new FormControl(''),
      language: new FormControl('', [Validators.required]),
      default: new FormControl('', [Validators.required]),
    });
  }

  /**
   * Fetch setting general data
   *
   * @private
   */
  private fetchModel() {
    this.settingService.getGeneral().subscribe({
      next: res => {
        if (res && res.success) {
          const language = res.data
            ? res.data.languagePreference.map((res: any) => ({ value: res, description: 'language.' + res }))
            : [{ value: this.defaultLanguage, description: 'language.' + this.defaultLanguage }];

          this.formGroup.setValue({
            appSubtitle: res.data ? res.data.applicationSubtitle ?? '' : '',
            language: language,
            default: res.data ? res.data.defaultLanguage : this.defaultLanguage,
          });
        }
      },
      error: err => {
        console.log(err);
      },
    });
  }

  /**
   * Take supported language value for the default language
   *
   * @param {any} event
   */
  onChange(event: any) {
    this.defaults = event;
    const lang = this.formGroup.controls['default'].value;

    const value = this.defaults ? this.defaults.find(res => res.value === lang) : [];

    if (!value) {
      this.formGroup.controls['default'].setValue('');
    }
  }

  /**
   * Saves or updates setting general data
   */
  save() {
    const appSubtitle = this.formGroup.controls['appSubtitle'].value;
    const language = this.formGroup.controls['language']
      ? this.formGroup.controls['language'].value.map((res: any) => {
          return res.value;
        })
      : [];
    const defaultLanguage = this.formGroup.controls['default'].value;

    if (this.formGroup.valid) {
      this.settingService.updateGeneral(appSubtitle, language, defaultLanguage).subscribe({
        next: res => {
          if (res && res.success) {
            this.languageService.switchLanguage(defaultLanguage, true);
            this.router.navigate(['/administration/settings']);
            this.alerts.setSuccess(_('setting.msg.general_setting_saved'));
          } else {
            this.alerts.setError(_('setting.msg.general_setting_save.error'));
          }
        },
        error: err => {
          this.alerts.setError(_('setting.msg.general_setting_save.error'));
        },
      });
    }
  }
}
