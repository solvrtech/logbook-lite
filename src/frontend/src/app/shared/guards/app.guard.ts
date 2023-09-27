import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { Observable, map } from 'rxjs';
import { AppService } from 'src/app/apps/services/apps/app.service';

@Injectable()
export class AppGuard implements CanActivate {
  constructor(private appService: AppService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> {
    const appId = route.params['id'];

    return this.appService.fetchCurrentApp(appId).pipe(
      map(app => {
        if (app != null) {
          return true;
        } else {
          this.router.navigate(['/'], { queryParams: { returnUrl: state.url } });
          return false;
        }
      })
    );
  }
}
