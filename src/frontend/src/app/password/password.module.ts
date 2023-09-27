import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatProgressSpinnerModule } from '@angular/material/progress-spinner';
import { LoadingBarHttpClientModule } from '@ngx-loading-bar/http-client';
import { SharedModule } from '../shared/shared.module';
import { SetPasswordComponent } from './pages/set-password/set-password.component';
import { PasswordRoutingModule } from './password-routing.module';

@NgModule({
  declarations: [SetPasswordComponent],
  imports: [
    CommonModule,
    PasswordRoutingModule,
    SharedModule.forRoot(),
    ReactiveFormsModule,
    MatProgressSpinnerModule,
    MatButtonModule,
    LoadingBarHttpClientModule,
  ],
})
export class PasswordModule {}
