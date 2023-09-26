import { Component, Input } from '@angular/core';
import { BreadCrumb } from '../../interfaces/common.interface';
import { BreadcrumbService } from '../../services/breadcrumb/breadcrumb.service';

@Component({
  selector: 'app-breadcrumb',
  template: '',
})
export class BreadcrumbComponent {
  @Input() set breadCrumb(newBreadCrumb: BreadCrumb[]) {
    this.breadCrumbService.setBreadCrumb(newBreadCrumb);
  }

  constructor(private breadCrumbService: BreadcrumbService) {}
}
