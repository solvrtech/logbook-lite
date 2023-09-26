import { Component, Input, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs/operators';
import { Team } from 'src/app/administration/interfaces/team.interface';
import { User } from 'src/app/administration/interfaces/user.interface';
import { RoleService } from 'src/app/administration/services/role.service';
import { TeamService } from 'src/app/administration/services/team.service';
import { UserService } from 'src/app/administration/services/user.service';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { BreadCrumb, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-my-team-detail',
  templateUrl: './my-team-detail.component.html',
  styleUrls: ['./my-team-detail.component.scss'],
})
export class MyTeamDetailComponent extends BaseSecurePageComponent implements OnInit {
  // if set, this will override the default `pagePermissions`
  @Input() permissions!: string[];

  @Input() returnUrl?: string;

  @Input() breadCrumbs!: BreadCrumb[];

  team!: Team;
  users!: User[];

  override get pagePermissions(): string[] {
    return this.permissions ?? [AppRole.ROLE_STANDARD];
  }

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: '/main-menu/my-teams',
  };

  // Breadcrumb for this page
  pageBreadCrumbs: BreadCrumb[] = [
    {
      url: '/main-menu/my-teams',
      label: 'title.my_teams',
    },
    {
      url: '',
      label: 'common.detail',
    },
  ];

  constructor(
    override roleService: RoleService,
    private teamService: TeamService,
    private activatedRoute: ActivatedRoute,
    private userService: UserService,
    private appSharedService: AppSharedService
  ) {
    super(roleService);
  }

  onInit(): void {
    this.userTeam();
    this.activatedRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      setTimeout(() => {
        if (params['id']) {
          // detail mode
          this.fetchModel(params['id']);
        }
      });
    });
  }

  /**
   * Fetch team data with the given id
   *
   * @param {number} id : team id
   * @private
   */
  private fetchModel(id: number) {
    this.teamService.getTeamById(id).subscribe({
      next: res => {
        if (res) {
          this.team = res;
          this.pageState.state = 'loaded';
        } else {
          this.pageState.state = 'error';
          this.pageState.message = _('error.msg.error_while_loading_team_data');
        }
      },
      error: err => {
        this.pageState.state = 'error';
        this.pageState.message = _('error.msg.error_while_loading_team_data');
      },
    });
  }

  userTeam() {
    this.userService.getUserStandard().subscribe({
      next: res => {
        if (res && res.success) {
          this.users = res.data;
        }
      },
      error: err => {
        console.log(err);
      },
    });
  }

  /**
   * Return current icon app
   *
   * @param {string} type
   * @return
   */
  appIcon(type: string) {
    return this.appSharedService.getAppIcon(type);
  }
}
