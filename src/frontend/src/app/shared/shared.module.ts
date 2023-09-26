import { CommonModule } from '@angular/common';
import { ModuleWithProviders, NgModule } from '@angular/core';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatChipsModule } from '@angular/material/chips';
import { MatOptionModule } from '@angular/material/core';
import { MatDatepickerModule } from '@angular/material/datepicker';
import { MatDialogModule } from '@angular/material/dialog';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatMenuModule } from '@angular/material/menu';
import { MatPaginatorIntl, MatPaginatorModule } from '@angular/material/paginator';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatRadioModule } from '@angular/material/radio';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatSnackBarModule } from '@angular/material/snack-bar';
import { MatTableModule } from '@angular/material/table';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatTooltipModule } from '@angular/material/tooltip';
import { RouterModule } from '@angular/router';
import { TranslateModule } from '@ngx-translate/core';
import { AngularResizeEventModule } from 'angular-resize-event';
import { ImageCropperModule } from 'ngx-image-cropper';
import { NgxMatSelectSearchModule } from 'ngx-mat-select-search';
import { NgxMaterialTimepickerModule } from 'ngx-material-timepicker';
import { AuthService } from '../login/services/auth/auth.service';
import { BreadcrumbComponent } from './component/breadcrumb/breadcrumb.component';
import { ConfirmDialogComponent } from './component/confirm-dialog/confirm-dialog.component';
import { FormBackButtonComponent } from './component/generic-form/form-back-button.component';
import { FormCreateButtonComponent } from './component/generic-form/form-create-button.component';
import { FormInputComponent } from './component/generic-form/form-input.component';
import { FormLogoUploadComponent } from './component/generic-form/form-logo-upload.component';
import { FormPasswordComponent } from './component/generic-form/form-password.component';
import { FormSaveButtonComponent } from './component/generic-form/form-save-button.component';
import { FormSelectSearchMultipleComponent } from './component/generic-form/form-select-search-multiple.component';
import { FormSelectSearchComponent } from './component/generic-form/form-select-search.component';
import { FormSelectServerSideComponent } from './component/generic-form/form-select-server-side.component';
import { FormTextAreaComponent } from './component/generic-form/form-textarea.component';
import { FormTimepickerComponent } from './component/generic-form/form-timepicker.component';
import { FormToggleComponent } from './component/generic-form/form-toggle.component';
import { ImageCropperDialogComponent } from './component/image-cropper-dialog/image-cropper-dialog.component';
import { LanguageSelectorComponent } from './component/language-selector/language-selector.component';
import { LogoComponent } from './component/logo/logo.component';
import { MailConnectionDialogComponent } from './component/mail-connection-dialog/mail-connection-dialog.component';
import { PageDetailComponent } from './component/page-detail/page-detail.component';
import { PageComponent } from './component/page/page.component';
import { SearchComponent } from './component/search/search.component';
import { TableComponent } from './component/table/table.component';
import { TitleComponent } from './component/title/title.component';
import { AppGuard } from './guards/app.guard';
import { AuthGuard } from './guards/auth.guard';
import { AuthTwfGuard } from './guards/authtwf.guard';
import { FormatNumberPipe } from './pipes/format-number.pipe';
import { NoNullPipe } from './pipes/nonull.pipe';
import { AlertsService } from './services/alerts/alerts.service';
import { LanguageService } from './services/language/language.service';
import { MailSettingService } from './services/mail-setting/mail-setting.service';
import { MatPaginatorI18nService } from './services/mat-paginator-i18n/mat-paginator-i18n.service';

@NgModule({
  declarations: [
    PageComponent,
    TitleComponent,
    LanguageSelectorComponent,
    TableComponent,
    NoNullPipe,
    PageDetailComponent,
    FormBackButtonComponent,
    SearchComponent,
    FormCreateButtonComponent,
    FormInputComponent,
    FormPasswordComponent,
    FormSaveButtonComponent,
    FormSelectSearchComponent,
    FormToggleComponent,
    ConfirmDialogComponent,
    FormTextAreaComponent,
    BreadcrumbComponent,
    FormSelectServerSideComponent,
    FormSelectSearchMultipleComponent,
    FormatNumberPipe,
    FormLogoUploadComponent,
    ImageCropperDialogComponent,
    LogoComponent,
    FormTimepickerComponent,
    MailConnectionDialogComponent,
  ],
  imports: [
    CommonModule,
    MatSnackBarModule,
    TranslateModule,
    MatButtonModule,
    MatIconModule,
    MatMenuModule,
    MatPaginatorModule,
    MatTableModule,
    MatToolbarModule,
    MatRadioModule,
    MatSlideToggleModule,
    MatChipsModule,
    MatCardModule,
    MatTooltipModule,
    MatDividerModule,
    MatProgressSpinnerModule,
    FormsModule,
    ReactiveFormsModule,
    RouterModule,
    MatFormFieldModule,
    MatOptionModule,
    MatDatepickerModule,
    MatInputModule,
    NgxMatSelectSearchModule,
    MatSelectModule,
    AngularResizeEventModule,
    ImageCropperModule,
    MatDialogModule,
    MatCheckboxModule,
    NgxMaterialTimepickerModule,
  ],
  exports: [
    PageComponent,
    TitleComponent,
    TranslateModule,
    LanguageSelectorComponent,
    TableComponent,
    NoNullPipe,
    FormBackButtonComponent,
    PageDetailComponent,
    SearchComponent,
    FormCreateButtonComponent,
    FormInputComponent,
    FormPasswordComponent,
    FormSaveButtonComponent,
    FormSelectSearchComponent,
    FormToggleComponent,
    FormTextAreaComponent,
    BreadcrumbComponent,
    FormSelectServerSideComponent,
    FormSelectSearchMultipleComponent,
    FormLogoUploadComponent,
    MatDialogModule,
    ImageCropperDialogComponent,
    FormTimepickerComponent,
  ],
  entryComponents: [ConfirmDialogComponent],
})
export class SharedModule {
  static forRoot(): ModuleWithProviders<SharedModule> {
    return {
      ngModule: SharedModule,
      providers: [
        AlertsService,
        LanguageService,
        AuthService,
        AuthGuard,
        AuthTwfGuard,
        NoNullPipe,
        MailSettingService,
        AppGuard,
        {
          provide: MatPaginatorIntl,
          useClass: MatPaginatorI18nService,
        },
      ],
    };
  }
}
