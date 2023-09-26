import { Component, Input } from '@angular/core';

/**
 * Generic wrapper component for back button
 */
@Component({
  selector: 'app-back-button',
  templateUrl: './form-back-button.component.html',
})
export class FormBackButtonComponent {
  @Input() link?: string;
  @Input() queryParams: any;
}
