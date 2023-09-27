import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { RoleService } from 'src/app/administration/services/role.service';
import { AppLogo, AppStandard } from 'src/app/apps/interfaces/app.interface';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { AppSharedService } from 'src/app/main-menu/services/app-shared/app-shared.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { BreadCrumb, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-view',
  templateUrl: './app-view.component.html',
  styleUrls: ['./app-view.component.scss'],
})
export class AppViewComponent extends BaseSecurePageComponent implements OnInit {
  appId: string | null = null;
  name!: string;
  appLogo!: AppLogo;
  isTeamManager!: boolean;
  app: AppStandard | null = null;

  // Breadcrumb for this page
  breadCrumbs: BreadCrumb[] = [
    {
      url: '/main-menu/apps',
      label: 'title.apps',
    },
    {
      url: '',
      label: 'common.view',
    },
  ];

  /**
   * Get permission to show the app log`s page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: '/main-menu/apps',
  };

  constructor(
    protected override roleService: RoleService,
    private activateRoute: ActivatedRoute,
    private appService: AppService,
    private appSharedService: AppSharedService
  ) {
    super(roleService);
    this.appId = this.activateRoute.snapshot.paramMap.get('id');
    this.app = this.appService.currentApp;

    if (this.app != null) {
      this.name = this.app.name;
      this.appLogo = this.appSharedService.getAppLogo(this.app.type, this.app.appLogo);
      this.isTeamManager = this.app.isTeamManager;
    }
  }

  onInit(): void {
    this.pageState.state = 'loaded';
  }

  get isHealthSettingActive(): boolean {
    if (this.app == null) {
      return false;
    }

    return this.app.appHealthSetting != null ? this.app.appHealthSetting.isEnabled : false;
  }
}
