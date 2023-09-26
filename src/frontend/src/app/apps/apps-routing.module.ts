import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AppGuard } from '../shared/guards/app.guard';
import { AuthGuard } from '../shared/guards/auth.guard';
import { AlertEditorComponent } from './pages/app-alerts/alert-editor/alert-editor.component';
import { AlertViewComponent } from './pages/app-alerts/alert-view/alert-view.component';
import { AlertsComponent } from './pages/app-alerts/alerts/alerts.component';
import { AppEditorComponent } from './pages/app-editor/app-editor.component';
import { AppLogDetailComponent } from './pages/app-view/app-log-detail/app-log-detail.component';
import { AppViewComponent } from './pages/app-view/view/app-view.component';
import { AppsComponent } from './pages/apps/apps.component';

const routes: Routes = [
  {
    path: '',
    canActivate: [AuthGuard],
    children: [
      { path: '', component: AppsComponent },
      { path: 'create', component: AppEditorComponent },
      { path: 'settings/:id', component: AppEditorComponent },
      { path: 'view/:id', component: AppViewComponent, canActivate: [AppGuard] },
      { path: 'view/:id/log/:logId', component: AppLogDetailComponent },
      { path: 'alert/:id', component: AlertsComponent },
      { path: 'alert/:id/create', component: AlertEditorComponent },
      { path: 'alert/:id/edit/:alertId', component: AlertEditorComponent },
      { path: 'alert/:id/view/:viewId', component: AlertViewComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AppsRoutingModule {}
