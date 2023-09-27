import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { MatCardModule } from '@angular/material/card';
import { MainMenuModule } from '../main-menu/main-menu.module';
import { SharedModule } from '../shared/shared.module';
import { HealthRoutingModule } from './healths-routing.module';
import { HealthsComponent } from './pages/healths/healths.component';

@NgModule({
  declarations: [HealthsComponent],
  imports: [CommonModule, HealthRoutingModule, SharedModule.forRoot(), MainMenuModule, MatCardModule],
})
export class HealthSModule {}
