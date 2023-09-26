import { CommonModule } from '@angular/common';
import { HttpClientModule } from '@angular/common/http';
import { NgModule } from '@angular/core';
import { ReactiveFormsModule } from '@angular/forms';
import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatDialogModule } from '@angular/material/dialog';
import { MatIconModule } from '@angular/material/icon';
import { MatListModule } from '@angular/material/list';
import { MatTabsModule } from '@angular/material/tabs';
import { MatTooltipModule } from '@angular/material/tooltip';
import { MainMenuModule } from '../main-menu/main-menu.module';
import { MyTeamsModule } from '../my-teams/my-teams.module';
import { SharedModule } from '../shared/shared.module';
import { AdministrationRoutingModule } from './administration-routing.module';
import { GeneralSettingsComponent } from './component/general-settings/general-settings.component';
import { SecuritySettingsComponent } from './component/security-settings/security-settings.component';
import { MyProfileComponent } from './pages/my-profile/my-profile.component';
import { SettingsComponent } from './pages/settings/settings.component';
import { TeamDetailComponent } from './pages/teams/team-detail/team-detail.component';
import { TeamEditorComponent } from './pages/teams/team-editor/team-editor.component';
import { TeamsComponent } from './pages/teams/teams/teams.component';
import { UserEditorComponent } from './pages/users/user-editor/user-editor.component';
import { UsersComponent } from './pages/users/users/users.component';

@NgModule({
  declarations: [
    UsersComponent,
    UserEditorComponent,
    MyProfileComponent,
    TeamsComponent,
    TeamEditorComponent,
    SettingsComponent,
    GeneralSettingsComponent,
    SecuritySettingsComponent,
    TeamDetailComponent,
  ],
  imports: [
    CommonModule,
    AdministrationRoutingModule,
    SharedModule.forRoot(),
    MatTabsModule,
    MatListModule,
    MatIconModule,
    MatCardModule,
    ReactiveFormsModule,
    HttpClientModule,
    MatDialogModule,
    MatButtonModule,
    MainMenuModule,
    MatTooltipModule,
    MyTeamsModule,
  ],
  exports: [GeneralSettingsComponent, SecuritySettingsComponent],
})
export class AdministrationModule {}
