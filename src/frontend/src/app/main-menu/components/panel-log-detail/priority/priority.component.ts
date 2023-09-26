import { Component, Input } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { DROPDOWN_PRIORITIES } from 'src/app/main-menu/data/dropdown-config.data';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';

@Component({
  selector: 'app-priority',
  templateUrl: './priority.component.html',
  styleUrls: ['./priority.component.scss'],
})
export class PriorityComponent extends BaseComponent {
  // if set, this will override the default priority
  @Input() priority!: string;

  // if set, this will override the default permission when this is true
  @Input() hasPermission!: boolean;

  // if set, this will be used to update priority for this component (and `edit` will be true)
  @Input() idLog!: string;

  formGroup!: FormGroup;
  isLoading: boolean = false;
  editMode: boolean = false;

  priorities: FormDropdown[] = DROPDOWN_PRIORITIES;

  constructor(private fb: FormBuilder, private logService: LogService) {
    super();

    this.initFromGroup();
  }

  /**
   * Setup form controls and initial validators
   *
   * @private
   */
  private initFromGroup() {
    this.formGroup = this.fb.group({
      priority: new FormControl(''),
    });
  }

  /**
   * Saves or updates priority data
   */
  save() {
    const priority = this.formGroup.controls['priority'].value;

    if (this.formGroup.valid) {
      this.isLoading = true;

      this.logService.updatePriority(this.idLog, priority).subscribe({
        next: res => {
          if (res && res.success) {
            setTimeout(() => {
              this.priority = res.data.priority ? res.data.priority : '';
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
   * Show form priority
   */
  onUpdate() {
    this.formGroup.controls['priority'].setValue(this.priority);
    this.editMode = true;
  }

  /**
   * Hide form priority
   */
  onClose() {
    this.editMode = false;
    this.isLoading = false;
  }
}
