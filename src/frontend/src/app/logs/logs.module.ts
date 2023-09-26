import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { ReactiveFormsModule } from '@angular/forms';
import { MatCardModule } from '@angular/material/card';
import { MatChipsModule } from '@angular/material/chips';
import { MatDividerModule } from '@angular/material/divider';
import { MatFormFieldModule } from '@angular/material/form-field';
import { MatIconModule } from '@angular/material/icon';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { HighlightModule } from 'ngx-highlightjs';
import { MainMenuModule } from '../main-menu/main-menu.module';
import { SharedModule } from '../shared/shared.module';
import { LogsRoutingModule } from './logs-routing.module';
import { LogDetailComponent } from './pages/log-detail/log-detail.component';
import { LogsComponent } from './pages/logs/logs.component';

@NgModule({
  declarations: [LogsComponent, LogDetailComponent],
  imports: [
    CommonModule,
    LogsRoutingModule,
    SharedModule.forRoot(),
    MatCardModule,
    MatDividerModule,
    ReactiveFormsModule,
    MatFormFieldModule,
    MatProgressSpinnerModule,
    HighlightModule,
    MatChipsModule,
    MatIconModule,
    MainMenuModule,
  ],
})
export class LogsModule {}
