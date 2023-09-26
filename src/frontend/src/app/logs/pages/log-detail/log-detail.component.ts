import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { marker as _ } from '@biesbjerg/ngx-translate-extract-marker';
import { takeUntil } from 'rxjs/operators';
import { RoleService } from 'src/app/administration/services/role.service';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { AppService } from 'src/app/apps/services/apps/app.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { formatDateTime } from 'src/app/shared/helpers/date.helper';
import { BreadCrumb, FormPageProps } from 'src/app/shared/interfaces/common.interface';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { Comment, Log } from '../../interfaces/log.interface';

@Component({
  selector: 'app-log-detail',
  templateUrl: './log-detail.component.html',
  styleUrls: ['./log-detail.component.scss'],
})
export class LogDetailComponent extends BaseSecurePageComponent implements OnInit {
  log!: Log;
  comments!: Comment[];
  date!: string;

  // Breadcrumb for this page
  breadCrumbs: BreadCrumb[] = [
    {
      url: '/main-menu/logs',
      label: 'title.logs',
    },
    {
      url: ``,
      label: 'common.detail',
    },
  ];

  // the page's state
  override pageState: FormPageProps = {
    state: 'loading',
    returnUrl: `/main-menu/logs`,
  };

  /**
   * Get permission to show the log detail page
   *
   * @returns {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(
    protected override roleService: RoleService,
    private activatedRoute: ActivatedRoute,
    private appService: AppService,
    private settingService: SettingService
  ) {
    super(roleService);
  }

  onInit(): void {
    this.activatedRoute.params.pipe(takeUntil(this.onDestroy$)).subscribe(params => {
      setTimeout(() => {
        if (params['id']) {
          this.fetchModel(params['id']);
        }
      });
    });
  }

  /**
   * Fetch app log data with the given id
   *
   * @param {string} id : log id
   * @private
   */
  private fetchModel(id: string) {
    this.appService.getLogDetail(id).subscribe({
      next: res => {
        if (res && res.success) {
          this.log = res.data.log;
          this.comments = res.data.comments;

          this.date = this.log.dateTime ? formatDateTime({ date: this.log.dateTime }, this.settingService) : '';
          this.pageState.state = 'loaded';
        } else {
          this.pageState.state = 'error';
          this.pageState.message = _('error.msg.error_while_loading_log_view_data');
        }
      },
      error: err => {
        this.pageState.state = 'error';
        this.pageState.message = _('error.msg.error_while_loading_log_view_data');
      },
    });
  }
}
