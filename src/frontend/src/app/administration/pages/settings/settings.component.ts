import { Component, OnInit } from '@angular/core';
import { BaseSecurePageComponent } from 'src/app/shared/component/bases/base-secure-page.component';
import { AppRole } from 'src/app/starter/data/permissions.data';
import { RoleService } from '../../services/role.service';
import { SettingService } from '../../services/settings/setting.service';

@Component({
  selector: 'app-settings',
  templateUrl: './settings.component.html',
  styleUrls: ['./settings.component.scss'],
})
export class SettingsComponent extends BaseSecurePageComponent implements OnInit {
  selectedIndex: number = this.settingService.selectedIndex;

  /**
   * Get permission to setting page
   *
   * @return {string[]}
   */
  get pagePermissions(): string[] {
    return [AppRole.ROLE_ADMIN];
  }

  get showPageSetting(): boolean {
    return this.isCurrentUserHas([AppRole.ROLE_ADMIN]);
  }

  constructor(protected override roleService: RoleService, public settingService: SettingService) {
    super(roleService);
  }

  onInit(): void {
    this.pageState.state = 'loaded';
  }
}
