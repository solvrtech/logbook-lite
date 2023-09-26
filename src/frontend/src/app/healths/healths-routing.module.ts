import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '../shared/guards/auth.guard';
import { HealthsComponent } from './pages/healths/healths.component';

const routes: Routes = [
  {
    path: '',
    canActivate: [AuthGuard],
    children: [{ path: '', component: HealthsComponent }],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class HealthRoutingModule {}
