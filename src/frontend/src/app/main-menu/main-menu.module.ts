import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';
import { MainMenuRoutingModule } from './main-menu-routing.module';

import { ClipboardModule } from '@angular/cdk/clipboard';
import { DragDropModule } from '@angular/cdk/drag-drop';
import { FormsModule, ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatChipsModule } from '@angular/material/chips';
import { MatDialogModule } from '@angular/material/dialog';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { MatSelectModule } from '@angular/material/select';
import { MatSlideToggleModule } from '@angular/material/slide-toggle';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { LoadingBarModule } from '@ngx-loading-bar/core';
import { LoadingBarHttpClientModule } from '@ngx-loading-bar/http-client';
import { HighlightModule } from 'ngx-highlightjs';
import { SharedModule } from '../shared/shared.module';
import { DeleteAppComponent } from './components/app-settings/delete-app/delete-app.component';
import { HealthSettingComponent } from './components/app-settings/health-setting/health-setting.component';
import { MailSettingComponent } from './components/app-settings/mail-setting/mail-setting.component';
import { TeamsComponent } from './components/app-settings/teams/teams.component';
import { DetailLogComponent } from './components/detail-log/detail-log.component';
import { DetailLogDialogComponent } from './components/dialogs/detail-log-dialog/DetailLogDialogComponent';
import { HealthCheckDialogComponent } from './components/dialogs/health-check-dialog/health-check-dialog.component';
import { HealthDialogComponent } from './components/dialogs/health-dialog/health-dialog.component';
import { HealthComponent } from './components/health/health.component';
import { logComponent } from './components/log/log.component';
import { AssigneeComponent } from './components/panel-log-detail/assignee/assignee.component';
import { PriorityComponent } from './components/panel-log-detail/priority/priority.component';
import { StatusComponent } from './components/panel-log-detail/status/status.component';
import { TagsComponent } from './components/panel-log-detail/tags/tags.component';
import { MainMenuComponent } from './pages/main-menu/main-menu.component';

@NgModule({
  declarations: [
    DetailLogDialogComponent,
    HealthSettingComponent,
    MailSettingComponent,
    TeamsComponent,
    DeleteAppComponent,
    HealthComponent,
    HealthDialogComponent,
    HealthCheckDialogComponent,
    MainMenuComponent,
    DetailLogComponent,
    StatusComponent,
    AssigneeComponent,
    PriorityComponent,
    TagsComponent,
    logComponent,
  ],
  imports: [
    CommonModule,
    MainMenuRoutingModule,
    SharedModule.forRoot(),
    MatCardModule,
    ReactiveFormsModule,
    MatButtonModule,
    MatFormFieldModule,
    MatInputModule,
    MatDialogModule,
    MatIconModule,
    MatTooltipModule,
    ClipboardModule,
    MatTabsModule,
    MatDividerModule,
    MatSlideToggleModule,
    MatProgressSpinnerModule,
    MatChipsModule,
    HighlightModule,
    MatSelectModule,
    LoadingBarModule,
    LoadingBarHttpClientModule,
    DragDropModule,
    MatCheckboxModule,
    FormsModule,
  ],
  exports: [
    logComponent,
    HealthSettingComponent,
    MailSettingComponent,
    DeleteAppComponent,
    DetailLogComponent,
    StatusComponent,
    AssigneeComponent,
    PriorityComponent,
    TagsComponent,
    TeamsComponent,
    HealthComponent,
  ],
})
export class MainMenuModule {}
