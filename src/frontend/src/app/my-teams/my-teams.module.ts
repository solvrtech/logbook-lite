import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { MatCardModule } from '@angular/material/card';
import { MatDividerModule } from '@angular/material/divider';
import { MainMenuModule } from '../main-menu/main-menu.module';
import { SharedModule } from '../shared/shared.module';
import { MyTeamsRoutingModule } from './my-teams-routing.module';
import { MyTeamDetailComponent } from './pages/my-team-detail/my-team-detail.component';
import { MyTeamsComponent } from './pages/my-teams/my-teams.component';

@NgModule({
  declarations: [MyTeamsComponent, MyTeamDetailComponent],
  exports: [MyTeamDetailComponent],
  imports: [
    CommonModule,
    MyTeamsRoutingModule,
    MainMenuModule,
    SharedModule.forRoot(),
    MatCardModule,
    MatDividerModule,
  ],
})
export class MyTeamsModule {}
