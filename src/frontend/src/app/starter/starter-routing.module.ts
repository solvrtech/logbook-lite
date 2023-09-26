import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { StarterComponent } from './pages/starter/starter.component';

const routes: Routes = [
  {
    path: '',
    component: StarterComponent,
    children: [
      {
        path: '',
        redirectTo: 'main-menu/logs',
        pathMatch: 'full',
      },
      {
        path: 'administration',
        loadChildren: () => import('../administration/administration.module').then(m => m.AdministrationModule),
      },
      {
        path: 'main-menu',
        loadChildren: () => import('../main-menu/main-menu.module').then(m => m.MainMenuModule),
      },
      {
        path: 'messages',
        loadChildren: () => import('../messages/messages.module').then(m => m.MessagesModule),
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class StarterRoutingModule {}
