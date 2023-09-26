import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthTwfGuard } from '../shared/guards/authtwf.guard';
import { LoginComponent } from './pages/login/login.component';
import { ResetPasswordComponent } from './pages/reset-password/reset-password.component';
import { TwoFactorAuthComponent } from './pages/two-factor-auth/two-factor-auth.component';

const routes: Routes = [
  {
    path: '',
    children: [
      { path: '', component: LoginComponent },
      { path: 'reset', component: ResetPasswordComponent },
      { path: 'two-factor', component: TwoFactorAuthComponent, canActivate: [AuthTwfGuard] },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class LoginRoutingModule {}
