import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { MainMenuComponent } from './pages/main-menu/main-menu.component';

const routes: Routes = [
  {
    path: '',
    component: MainMenuComponent,
    children: [
      {
        path: 'logs',
        loadChildren: () => import('../logs/logs.module').then(m => m.LogsModule),
      },
      {
        path: 'healths',
        loadChildren: () => import('../healths/healths.module').then(m => m.HealthSModule),
      },
      {
        path: 'apps',
        loadChildren: () => import('../apps/apps.module').then(m => m.AppsModule),
      },
      {
        path: 'my-teams',
        loadChildren: () => import('../my-teams/my-teams.module').then(m => m.MyTeamsModule),
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MainMenuRoutingModule {}
