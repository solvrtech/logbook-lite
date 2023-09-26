import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { MatDialog } from '@angular/material/dialog';
import { NavigationEnd, Router } from '@angular/router';
import { BehaviorSubject, Subscription, filter, map } from 'rxjs';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { MessageService } from 'src/app/messages/services/message/message.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { LanguageService } from 'src/app/shared/services/language/language.service';
import { AutoRefreshDialogComponent } from '../dialog/auto-refresh-dialog/auto-refresh-dialog.component';

@Component({
  selector: 'app-navbar',
  templateUrl: './navbar.component.html',
  styleUrls: ['./navbar.component.scss'],
})
export class NavbarComponent extends BaseComponent implements OnInit {
  public routerEventSubcription!: Subscription;

  @Output() onMenuIconClick = new EventEmitter();
  @Input() menuOpened: boolean | null = true;
  @Input() languages!: any;
  @Input() defaultLanguage!: string;

  currentUrl = '.';
  userName$ = new BehaviorSubject('');

  get autoRefreshMessage() {
    return this.messageService.messageApiService.autoRefresh;
  }

  get totalMessage() {
    return this.messageService.messageApiService.totalMessage;
  }

  constructor(
    public languageService: LanguageService,
    public authService: AuthService,
    private router: Router,
    private dialog: MatDialog,
    private messageService: MessageService
  ) {
    super();
  }

  ngOnInit(): void {
    this.routerEventSubcription = this.subscribeToRouterUrlChange();
  }

  logout() {
    this.authService.logout();
  }

  subscribeToRouterUrlChange(): Subscription {
    return this.router.events
      .pipe(
        filter((e): e is NavigationEnd => e instanceof NavigationEnd),
        map(e => e.urlAfterRedirects)
      )
      .subscribe(urlPath => {
        this.currentUrl = urlPath;
      });
  }

  toggleMenu() {
    this.onMenuIconClick.emit();
  }

  showAutoRefreshDialog() {
    this.dialog.open(AutoRefreshDialogComponent, { width: '35em' });
  }
}
