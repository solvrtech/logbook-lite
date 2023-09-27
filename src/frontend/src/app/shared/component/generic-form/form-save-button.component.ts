import { Component, EventEmitter, Input, Output } from '@angular/core';

/**
 * Generic wrapper component for save button
 */
@Component({
  selector: 'app-save-button',
  templateUrl: './form-save-button.component.html',
})
export class FormSaveButtonComponent {
  @Input() disabled?: boolean;
  @Output() save = new EventEmitter<any>();

  onClick(event: any) {
    this.save.emit(event);
  }
}
