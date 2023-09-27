import { Component } from '@angular/core';
import { BreadCrumb } from 'src/app/shared/interfaces/common.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-team-detail',
  templateUrl: './team-detail.component.html',
  styleUrls: ['./team-detail.component.scss'],
})
export class TeamDetailComponent {
  returnUrl = '/administration/teams';

  // Breadcrumb for this page
  breadCrumbs: BreadCrumb[] = [
    {
      url: this.returnUrl,
      label: 'title.teams',
    },
    {
      url: '',
      label: 'common.detail',
    },
  ];

  constructor() {}

  /**
   * Get permission to details team page
   *
   * @return {string}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN];
  }
}
