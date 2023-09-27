import { Component, Input, OnInit } from '@angular/core';
import { takeUntil } from 'rxjs/operators';
import { BaseFormFieldComponent } from './form-field.base';

/**
 * Generic wrapper component for mat-slide-toggle using reactive form driven technique.
 */
@Component({
  selector: 'app-form-toggle',
  templateUrl: './form-toggle.component.html',
})
export class FormToggleComponent extends BaseFormFieldComponent implements OnInit {
  @Input() checked: boolean = true;
  ngOnInit() {
    // this.valueChanged.emit(this.control.value);
    this.control.valueChanges.pipe(takeUntil(this.onDestroy$)).subscribe((value: any) => this.valueChanged.emit(value));

    // emit the value for the first time if initial value is given
    this.valueChanged.emit(this.control.value);
  }
}
