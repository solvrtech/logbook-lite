import { CommonModule } from '@angular/common';
import { NgModule } from '@angular/core';

import { MatButtonModule } from '@angular/material/button';
import { MatCardModule } from '@angular/material/card';
import { MatCheckboxModule } from '@angular/material/checkbox';
import { MatIconModule } from '@angular/material/icon';
import { SharedModule } from '../shared/shared.module';
import { MessagesRoutingModule } from './messages-routing.module';
import { MessagesComponent } from './pages/messages/messages.component';

@NgModule({
  declarations: [MessagesComponent],
  imports: [
    CommonModule,
    MessagesRoutingModule,
    SharedModule.forRoot(),
    MatCardModule,
    MatIconModule,
    MatButtonModule,
    MatCheckboxModule,
  ],
})
export class MessagesModule {}
