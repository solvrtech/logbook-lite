import { Component } from '@angular/core';
import {
  NavigationCancel,
  NavigationEnd,
  NavigationError,
  NavigationStart,
  RouteConfigLoadEnd,
  RouteConfigLoadStart,
  Router,
} from '@angular/router';
import { environment } from 'src/environments/environment';
import { NavigationService } from './shared/services/navigation/navigation.service';

@Component({
  selector: 'app-root',
  templateUrl: './app.component.html',
  styleUrls: ['./app.component.scss'],
})
export class AppComponent {
  /** async module loading variables */
  showLazyRouteLoader = false;
  asyncLoadCount = 0;
  navigationCount = 0;

  /** ngx-loading-bar configuration */
  ngxBarColor = environment.ngxBarColor;
  ngxSpinner = false;
  ngxHeight = '3px';

  constructor(private router: Router, private navigation: NavigationService) {}

  ngOnInit() {
    this.initAsyncModuleLoadingCheck();
  }

  private initAsyncModuleLoadingCheck() {
    // subscription starts on this root component, since we need complete navigation/route events
    this.router.events.subscribe(event => {
      if (event instanceof NavigationStart) {
        this.navigationCount++;
      } else if (
        event instanceof NavigationEnd ||
        event instanceof NavigationError ||
        event instanceof NavigationCancel
      ) {
        this.navigationCount--;
      }

      if (event instanceof NavigationEnd) {
        this.navigation.onNavigationEndChanged(event);
      } else if (event instanceof RouteConfigLoadStart) {
        this.asyncLoadCount++;
      } else if (event instanceof RouteConfigLoadEnd) {
        this.asyncLoadCount--;
      }

      this.showLazyRouteLoader = !!(this.navigationCount && this.asyncLoadCount);
    });
  }
}
