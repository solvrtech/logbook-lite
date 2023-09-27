import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { Comment, Log } from 'src/app/logs/interfaces/log.interface';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';
import { BreadCrumb, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';

@Component({
  selector: 'app-app-log-detail',
  templateUrl: './app-log-detail.component.html',
  styleUrls: ['./app-log-detail.component.scss'],
})
export class AppLogDetailComponent extends BaseSecurePageComponent implements OnInit {
  log!: Log;
  comments!: Comment[];
  date!: string;
  appId = this.activatedRoute.snapshot.params['id'];

  breadCrumbs: BreadCrumb[] = [
    {
      url: '/main-menu/apps',
      label: 'title.apps',
    },
    {
      url: `/main-menu/apps/view/${this.appId}`,
      label: 'common.view',
    },
    {
      url: '',
      label: 'common.detail',
    },
  ];

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: `/main-menu/apps/view/${this.appId}`,
  };

  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(
    protected override roleService: RoleService,
    private appService: AppService,
    private activatedRoute: ActivatedRoute,
    private settingService: SettingService
  ) {
    super(roleService);
  }

  onInit(): void {
    this.activatedRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      setTimeout(() => {
        if (params['id'] && params['logId']) {
          this.fetchAppLogDetail(params['id'], params['logId']);
        }
      });
    });
  }

  /**
   * Fetch app log detail data with the given `app id` and `log id`
   *
   * @param {number} appId : app id
   * @param {number} logId : log id
   */
  private fetchAppLogDetail(appId: string, logId: string) {
    this.appService.getAppLogDetail(appId, logId).subscribe({
      next: res => {
        if (res) {
          this.appService.selected = 1;
          this.log = res.log;
          this.comments = res.comments;
          this.date = this.log.dateTime ? formatDateTime({ date: this.log.dateTime }, this.settingService) : '';
          this.pageState.state = 'loaded';
        } else {
          this.pageState.state = 'error';
          this.pageState.message = _('error.msg.error_while_loading_app_log_data');
        }
      },
      error: err => {
        this.pageState.state = 'error';
        this.pageState.message = _('error.msg.error_while_loading_app_log_data');
      },
    });
  }
}
