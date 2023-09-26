import { BreakpointObserver, Breakpoints, BreakpointState } from '@angular/cdk/layout';
import { ChangeDetectorRef, Component, OnInit, ViewChild } from '@angular/core';
import { MatDrawer } from '@angular/material/sidenav';
import { Subject } from 'rxjs';
import { SettingService } from 'src/app/administration/services/settings/setting.service';
import { getFromLocalStorage, saveToLocalStorage } from 'src/app/shared/helpers/local-storage.helper';
import { AutoRefresh } from 'src/app/shared/interfaces/common.interface';
import { AutoRefreshService } from 'src/app/shared/services/auto-refresh/auto-refresh.service';
import { BreadcrumbService } from 'src/app/shared/services/breadcrumb/breadcrumb.service';
import { LogoService } from 'src/app/shared/services/logo/logo.service';
import { TitleService } from 'src/app/shared/services/title/title.service';
import { environment } from 'src/environments/environment';
import { Menu } from '../../data/menu.data';
import { MenuService } from '../../services/menu/menu.service';

@Component({
  selector: 'app-starter',
  templateUrl: './starter.component.html',
  styleUrls: ['./starter.component.scss'],
})
export class StarterComponent implements OnInit {
  @ViewChild('drawer', { static: true }) drawer!: MatDrawer;

  title$ = this.titleService.title$;
  hasRightSection$ = this.titleService.hasRightSection$;
  hasTitle$ = this.titleService.hasTitle$;
  hasNowrap$ = this.titleService.hasNowrap$;
  breadcrumbs$ = this.breadcrumbService.breadCrumbs$;
  logo$ = this.logoService.currentLogo$;
  menuItems!: Menu;
  menuOpened$ = new Subject<boolean>();
  isHandset = false;

  // settings
  subtitle: string = '';
  languages: any = environment.languageCodes;
  defaultLanguage: string = 'en';
  languageCodes = environment.languageCodes;

  // Auto refresh
  storage = environment.autoRefreshKey;

  // Auto refresh default
  autoRefresh: AutoRefresh = {
    enable: true,
    interval: 5000,
    enMessage: true,
    inMessage: 10000,
  };

  constructor(
    private breakpointObserver: BreakpointObserver,
    private titleService: TitleService,
    public menuService: MenuService,
    private breadcrumbService: BreadcrumbService,
    private changeDetector: ChangeDetectorRef,
    private settingService: SettingService,
    private logoService: LogoService,
    private autoRefreshService: AutoRefreshService
  ) {}

  ngOnInit() {
    this.breakpointObserver
      .observe([Breakpoints.Handset])
      .subscribe((state: BreakpointState) => (this.isHandset = state.matches));
    this.menuService.refresh();

    //Settings subtitle and languages
    this.fetchSetting();
    this.setAutoRefresh();

    if (getFromLocalStorage(this.storage) === null) {
      const autoRefresh = this.autoRefresh;
      saveToLocalStorage(this.storage, JSON.stringify(autoRefresh));
    }
  }

  toggleMenu() {
    this.drawer.toggle();
  }

  menuOpenedChanged(opened: boolean) {
    this.menuOpened$.next(opened);
  }

  onSidebarMenuClicked(link: string) {
    // close menu on mobile
    if (this.isHandset) {
      this.drawer.close();
    }
  }

  ngAfterContentChecked(): void {
    this.changeDetector.detectChanges();
  }

  /**
   * Fecth setting data
   *
   * @private
   */
  private fetchSetting() {
    this.settingService.getSettingsAll().subscribe({
      next: res => {
        if (res && res.success) {
          if (res.data) {
            // settings
            this.subtitle = res.data.generalSetting ? res.data.generalSetting.applicationSubtitle ?? '' : '';
            this.defaultLanguage = res.data.generalSetting ? res.data.generalSetting.defaultLanguage ?? 'en' : 'en';
            this.languages = res.data.generalSetting
              ? res.data.generalSetting.languagePreference ?? this.languageCodes
              : this.languageCodes;
          }
        }
      },
    });
  }

  private setAutoRefresh() {
    let storedAutoRefresh = this.autoRefresh;
    if (getFromLocalStorage(this.storage) === null) {
      storedAutoRefresh = this.autoRefresh;
    } else {
      storedAutoRefresh = JSON.parse(getFromLocalStorage(this.storage));
    }

    const autoRefresh: AutoRefresh = storedAutoRefresh;
    autoRefresh.enMessage = false;
    this.autoRefreshService.updateAutoRefresh(autoRefresh);
  }
}
