import { NgModule } from '@angular/core';
import { RouterModule, Routes } from '@angular/router';
import { AuthGuard } from '../shared/guards/auth.guard';
import { MyTeamDetailComponent } from './pages/my-team-detail/my-team-detail.component';
import { MyTeamsComponent } from './pages/my-teams/my-teams.component';

const routes: Routes = [
  {
    path: '',
    canActivate: [AuthGuard],
    children: [
      { path: '', component: MyTeamsComponent },
      { path: 'detail/:id', component: MyTeamDetailComponent },
    ],
  },
];

@NgModule({
  imports: [RouterModule.forChild(routes)],
  exports: [RouterModule],
})
export class MyTeamsRoutingModule {}
