import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { BehaviorSubject } from 'rxjs';
import { TEAM_MANAGER, TEAM_STANDARD } from 'src/app/administration/data/role.data';
import { Team } from 'src/app/administration/interfaces/team.interface';
import { RoleService } from 'src/app/administration/services/role.service';
import { TeamService } from 'src/app/administration/services/team.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { TableColumns, TableRowMenuItems } from 'src/app/shared/interfaces/table.interface';
import { AppPage, AppRole } from 'src/app/starter/data/permissions.data';
import { MyTeamsDataSource } from '../../datasources/my-teams.datasource';

@Component({
  selector: 'app-my-teams',
  templateUrl: './my-teams.component.html',
  styleUrls: ['./my-teams.component.scss'],
})
export class MyTeamsComponent extends BaseSecurePageComponent implements OnInit {
  dataSource!: MyTeamsDataSource;
  columns$ = new BehaviorSubject<TableColumns>([]);
  rowMenus$ = new BehaviorSubject<TableRowMenuItems>([]);

  override pageName: string = AppPage.TEAM;

  get pagePermissions(): string[] {
    return [AppRole.ROLE_STANDARD];
  }

  constructor(override roleService: RoleService, teamService: TeamService, private router: Router) {
    super(roleService);
    this.dataSource = new MyTeamsDataSource(teamService);
  }

  onInit(): void {
    this.setTableColumns();
    this.setTableRowMenus();
    this.pageState.state = 'loaded';
  }

  private setTableColumns() {
    this.columns$.next([
      { name: _('common.name'), key: 'name' },
      { name: _('common.members'), key: 'member' },
      {
        name: _('common.membership'),
        key: 'isTeamManager',
        getValue: (data: Team) => (data.isTeamManager ? 'team.' + TEAM_MANAGER : 'team.' + TEAM_STANDARD),
        badge: (data: Team) =>
          data.isTeamManager ? 'badge rounded-pill text-bg-primary' : 'badge rounded-pill text-bg-orange',
      },
    ]);
  }

  private setTableRowMenus() {
    this.rowMenus$.next([
      {
        label: 'common.detail',
        icon: 'info',
        action: ($e, row, newTab, windowFeatures) => this.onDetailRow($e, row, newTab, windowFeatures),
        hasContextMenu: true,
      },
    ]);
  }

  private onDetailRow(event: MouseEvent, row: Team, newTab: boolean | undefined, windowFeatures?: string) {
    if (newTab) {
      window.open(`/main-menu/my-teams/detail/${row.id}`, '_blank', windowFeatures);
    } else {
      this.router.navigate([`/main-menu/my-teams/detail/${row.id}`]);
    }
  }
}
