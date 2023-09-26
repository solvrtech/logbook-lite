import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { NavigationEnd } from '@angular/router';
import { Observable, takeUntil } from 'rxjs';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { LanguageService } from 'src/app/shared/services/language/language.service';
import { NavigationService } from 'src/app/shared/services/navigation/navigation.service';
import { Menu, MenuItems, menu } from '../../data/menu.data';
import { AutoRefreshDialogComponent } from '../dialog/auto-refresh-dialog/auto-refresh-dialog.component';

@Component({
  selector: 'app-left-sidebar',
  templateUrl: './left-sidebar.component.html',
  styleUrls: ['./left-sidebar.component.scss'],
})
export class LeftSidebarComponent extends BaseComponent implements OnInit {
  menus: MenuItems[] = [];
  panelOpenState = false;

  @Input() defaultLanguage!: string;
  @Input() languages!: any;
  @Input() menus$!: Observable<Menu>;
  @Output() menuClicked = new EventEmitter<string>();

  currentNavigation!: NavigationEnd;

  constructor(
    private navigationService: NavigationService,
    private authService: AuthService,
    private dialog: MatDialog,
    public languageService: LanguageService
  ) {
    super();
  }

  ngOnInit() {
    if (this.menus$ != null) {
      this.menus$.pipe(takeUntil(this.onDestroy$)).subscribe(menus => {
        this.menus = menus;
      });
    }

    this.navigationService.navigation$
      .pipe(takeUntil(this.onDestroy$))
      .subscribe(navigation => this.onNavigationEnd(navigation));
  }

  onMenuClicked(link?: string) {
    this.menuClicked.emit(link);
  }

  showAutoRefreshDialog() {
    this.dialog.open(AutoRefreshDialogComponent, { width: '35em' });
  }

  logout() {
    this.authService.logout();
  }

  private onNavigationEnd(navigation: NavigationEnd | null) {
    if (navigation) {
      this.currentNavigation = navigation;
      menu.forEach(m => this.traverseMenuForHighlight(m, this.currentNavigation.url));
    }
  }

  private traverseMenuForHighlight(menu: MenuItems, url: string) {
    if (typeof menu.link === 'undefined' && menu.prefix) {
      // for nested tree node
      if (menu.prefix && this.currentNavigation.url.startsWith(menu.prefix)) {
        menu.cssClasses = 'active';
      } else {
        menu.cssClasses = '';
      }

      if (menu.items && menu.items.length) {
        menu.items.forEach(item => this.traverseMenuForHighlight(item, url));
      }
    } else if (menu.link) {
      menu.cssClasses = this.currentNavigation.url.startsWith(menu.link) ? 'active' : '';
    }
  }
}
