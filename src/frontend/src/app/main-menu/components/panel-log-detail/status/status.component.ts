import { Component, Input } from '@angular/core';
import { FormBuilder, FormControl, FormGroup } from '@angular/forms';
import { LogService } from 'src/app/logs/services/logs/log.service';
import { DROPDOWN_STATUS_CONFIG } from 'src/app/main-menu/data/dropdown-config.data';
import { BaseComponent } from 'src/app/shared/component/bases/base.component';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';

@Component({
  selector: 'app-status',
  templateUrl: './status.component.html',
  styleUrls: ['./status.component.scss'],
})
export class StatusComponent extends BaseComponent {
  //if set, this will override the default status data
  @Input() status!: string;

  //if set, this will be used to update status for this component (and `edit` will be true)
  @Input() idLog!: string;

  // //if set, this will override the default permission for this form field
  @Input() hasPermission!: boolean;

  optionStatus: FormDropdown[] = DROPDOWN_STATUS_CONFIG;

  formGroup!: FormGroup;
  isLoading: boolean = false;
  editMode: boolean = false;

  constructor(private fb: FormBuilder, private logService: LogService) {
    super();
    this.initFormGroup();
  }

  /**
   * Setup form controls and initial validators
   */
  private initFormGroup() {
    this.formGroup = this.fb.group({
      status: new FormControl(''),
    });
  }

  /**
   * Saves or updates status data
   */
  save() {
    const status = this.formGroup.controls['status'].value;

    if (this.formGroup.valid) {
      this.isLoading = true;

      this.logService.updateStatus(this.idLog, status).subscribe({
        next: res => {
          if (res && res.success) {
            setTimeout(() => {
              this.status = res.data.status ? res.data.status : '';
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
   * Show form status
   */
  onUpdate() {
    this.formGroup.controls['status'].setValue(this.status);
    this.editMode = true;
  }

  /**
   * Hide form status
   */
  onClose() {
    this.editMode = false;
    this.isLoading = false;
  }
}
