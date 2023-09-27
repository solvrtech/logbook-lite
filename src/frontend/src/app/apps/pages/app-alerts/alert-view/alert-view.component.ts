import { Component, OnInit } from '@angular/core';
import { ActivatedRoute } from '@angular/router';
import { BreadCrumb } from 'src/app/shared/interfaces/common.interface';

@Component({
  selector: 'app-alert-view',
  templateUrl: './alert-view.component.html',
  styleUrls: ['./alert-view.component.scss'],
})
export class AlertViewComponent implements OnInit {
  appId = this.activatedRoute.snapshot.params['id'];
  viewId = this.activatedRoute.snapshot.params['viewId'];

  // Breadcrumb for this page
  breadCrumbs: BreadCrumb[] = [
    {
      url: '/main-menu/apps',
      label: 'title.apps',
    },
    {
      url: `/main-menu/apps/alert/${this.appId}`,
      label: 'common.alerts',
    },
    {
      url: '',
      label: 'common.view',
    },
  ];

  constructor(private activatedRoute: ActivatedRoute) {}

  ngOnInit(): void {}
}
