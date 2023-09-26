import { Component, Input, OnInit } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { User } from 'src/app/administration/interfaces/user.interface';
import { UserService } from 'src/app/administration/services/user.service';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';

@Component({
  selector: 'app-assignee',
  templateUrl: './assignee.component.html',
  styleUrls: ['./assignee.component.scss'],
})
export class AssigneeComponent extends BaseComponent implements OnInit {
  // if set, this will override the default assignee user's data model
  @Input() assignee!: User;

  // if set, this will override the default dropdown data for `users` field when this is true
  @Input() hasPermission!: boolean;

  // if set, this will be used to fetch user's data model for this component (and `edit` will be true)
  @Input() idLog!: string;

  formGroup!: FormGroup;
  isLoading: boolean = false;
  editMode: boolean = false;
  description!: string;

  assignees: FormDropdown[] = [];

  constructor(private fb: FormBuilder, private userService: UserService, private logService: LogService) {
    super();
  }

  ngOnInit(): void {
    if (this.assignee != null) this.description = this.assignee.name ? this.assignee.name : '';

    if (this.hasPermission) this.fetchUsersLog(this.idLog);

    // Setup form controls and initial validators
    this.formGroup = this.fb.group({
      assignee: new FormControl(this.assignee ? this.assignee.id : ''),
    });
  }

  /**
   * Build dropdown data for `user's` field
   *
   * @param {string} id
   * @private
   */
  private fetchUsersLog(id: string) {
    this.userService.getUsersLog(id).subscribe({
      next: res => {
        if (res) {
          this.assignees = res.data.map(
            (item: any) =>
              ({
                value: item.id,
                description: item.name,
              } as FormDropdown)
          );
        } else {
          this.assignees = [];
        }
      },
    });
  }

  /**
   * Saves or updates user assignee data
   */
  save() {
    const assignee = this.formGroup.controls['assignee'].value;

    if (this.formGroup.valid) {
      this.isLoading = true;

      this.logService.updateAssignee(this.idLog, assignee).subscribe({
        next: res => {
          if (res && res.success) {
            setTimeout(() => {
              this.assignee = res.data.assignee ? res.data.assignee : '';
              this.isLoading = false;
              this.editMode = false;
            }, 1000);
          } else {
            this.isLoading = true;
            this.editMode = true;
          }
        },
        error: err => {
          this.isLoading = true;
          this.editMode = true;
        },
      });
    }
  }

  /**
   * Show form assigned user
   */
  onUpdate() {
    this.editMode = true;
  }

  /**
   * Hide form assigned user
   */
  onClose() {
    this.editMode = false;
    this.isLoading = false;
  }
}
