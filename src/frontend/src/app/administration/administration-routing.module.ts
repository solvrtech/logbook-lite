import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '../shared/guards/auth.guard';
import { MyProfileComponent } from './pages/my-profile/my-profile.component';
import { SettingsComponent } from './pages/settings/settings.component';
import { TeamDetailComponent } from './pages/teams/team-detail/team-detail.component';
import { TeamEditorComponent } from './pages/teams/team-editor/team-editor.component';
import { TeamsComponent } from './pages/teams/teams/teams.component';
import { UserEditorComponent } from './pages/users/user-editor/user-editor.component';
import { UsersComponent } from './pages/users/users/users.component';

const routes: Routes = [
  {
    path: '',
    canActivate: [AuthGuard],
    children: [
      {
        path: 'users',
        children: [
          { path: '', component: UsersComponent },
          { path: 'create', component: UserEditorComponent },
          { path: 'edit/:id', component: UserEditorComponent },
        ],
      },
      { path: 'my-profile', component: MyProfileComponent },
      {
        path: 'teams',
        children: [
          { path: '', component: TeamsComponent },
          { path: 'create', component: TeamEditorComponent },
          { path: 'edit/:id', component: TeamEditorComponent },
          { path: 'details/:id', component: TeamDetailComponent },
        ],
      },
      {
        path: 'settings',
        children: [{ path: '', component: SettingsComponent }],
      },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class AdministrationRoutingModule {}
