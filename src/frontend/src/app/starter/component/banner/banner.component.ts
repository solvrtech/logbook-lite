import { DOCUMENT } from '@angular/common';
import { Component, Inject, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { takeUntil } from 'rxjs';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { MailSetting } from 'src/app/apps/interfaces/app.interface';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { AppRole } from '../../data/permissions.data';

@Component({
  selector: 'app-banner',
  templateUrl: './banner.component.html',
  styleUrls: ['./banner.component.scss'],
})
export class BannerComponent extends BaseComponent implements OnInit {
  mailSetting!: MailSetting;
  linkMailSetting: string = '';
  close: boolean = true;
  role: string = AppRole.ROLE_ADMIN;

  constructor(
    @Inject(DOCUMENT) private document: Document,
    private settingService: SettingService,
    private router: Router,
    public authService: AuthService
  ) {
    super();
  }

  ngOnInit(): void {
    if (this.authService.currentUser?.role === this.role) this.fetchSmtpSetting();
  }

  private fetchSmtpSetting() {
    this.settingService
      .listenOnSmtpSetting()
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(mailSetting => {
        if (mailSetting != null) {
          this.mailSetting = mailSetting;
          this.close = true;
        } else {
          this.close = false;
          this.linkMailSetting = this.document.location.origin;
        }
      });
  }

  onClose() {
    this.close = true;
  }

  onClick() {
    this.settingService.selectedIndex = 2;
    this.router.navigate(['/administration/settings']);
  }
}
