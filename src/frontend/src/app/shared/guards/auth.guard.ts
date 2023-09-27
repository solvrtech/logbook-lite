import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { Observable, map } from 'rxjs';
import { AuthService } from 'src/app/login/services/auth/auth.service';

@Injectable()
export class AuthGuard implements CanActivate {
  constructor(public authService: AuthService, private router: Router) {}

  canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> {
    return this.authService.fetchCurrentUser.pipe(
      map(res => {
        if (res) {
          return true;
        } else {
          if (this.authService.currentTwoFactor != null) {
            this.router.navigate(['/login/two-factor'], {
              queryParams: { returnUrl: state.url },
            });
          } else {
            this.router.navigate(['/login'], {
              queryParams: { returnUrl: state.url },
            });
          }
          return false;
        }
      })
    );
  }
}
