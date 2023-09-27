import { Component, OnInit } from '@angular/core';
import { AuthService } from 'src/app/login/services/auth/auth.service';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { RoleService } from '../../services/role.service';

@Component({
  selector: 'app-my-profile',
  templateUrl: './my-profile.component.html',
  styleUrls: ['./my-profile.component.scss'],
})
export class MyProfileComponent extends BaseSecurePageComponent implements OnInit {
  userId!: number | undefined;

  /**
   * Get permission to edit my profile page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN, AppRole.ROLE_STANDARD];
  }

  constructor(protected override roleService: RoleService, private authService: AuthService) {
    super(roleService);
  }

  onInit(): void {
    this.userId = this.authService.currentUser?.id;
  }
}
