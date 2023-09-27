import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatOptionModule } from '@angular/material/core';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatInputModule } from '@angular/material/input';
import { MatSelectModule } from '@angular/material/select';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MainMenuModule } from '../main-menu/main-menu.module';
import { SharedModule } from '../shared/shared.module';
import { AppsRoutingModule } from './apps-routing.module';
import { AlertEditorComponent } from './pages/app-alerts/alert-editor/alert-editor.component';
import { AlertViewComponent } from './pages/app-alerts/alert-view/alert-view.component';
import { AlertsComponent } from './pages/app-alerts/alerts/alerts.component';
import { AppEditorComponent } from './pages/app-editor/app-editor.component';
import { AppLogDetailComponent } from './pages/app-view/app-log-detail/app-log-detail.component';
import { AppViewComponent } from './pages/app-view/view/app-view.component';
import { AppsComponent } from './pages/apps/apps.component';

@NgModule({
  declarations: [
    AppsComponent,
    AppEditorComponent,
    AppViewComponent,
    AppLogDetailComponent,
    AlertsComponent,
    AlertEditorComponent,
    AlertViewComponent,
  ],
  imports: [
    CommonModule,
    AppsRoutingModule,
    SharedModule.forRoot(),
    MatCardModule,
    MatDividerModule,
    MatTabsModule,
    MatTooltipModule,
    MatIconModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatOptionModule,
    MatButtonModule,
    MatInputModule,
    MatSelectModule,
    MainMenuModule,
  ],
})
export class AppsModule {}
