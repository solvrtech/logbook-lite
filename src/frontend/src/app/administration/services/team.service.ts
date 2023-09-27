import { HttpClient } from '@angular/common/http';
import { Injectable } from '@angular/core';
import { map, Observable } from 'rxjs';
import { BaseApiService } from 'src/app/shared/component/bases/base-api.service';
import { TableLoadOption } from 'src/app/shared/component/bases/table.datasource';
import { FormDropdown } from 'src/app/shared/interfaces/common.interface';
import { PaginateData, Response } from 'src/app/shared/interfaces/response.interface';
import { environment } from 'src/environments/environment';
import { Team } from '../interfaces/team.interface';

@Injectable({
  providedIn: 'root',
})
export class TeamService extends BaseApiService {
  constructor(protected override http: HttpClient) {
    super(http);
  }

  getTeams(option?: TableLoadOption | any): Observable<PaginateData<Team>> {
    return this.get<PaginateData<Team>>(`${environment.teamsPath}`, option);
  }

  createTeam(name: string, user: any): Observable<Response> {
    return this.http.post<Response>(`${environment.teamsPath}/create`, { name, user }).pipe(
      map((res: Response) => {
        return res;
      })
    );
  }

  updateTeam(id: number, name: string, user: any): Observable<Response> {
    return this.http.put(`${environment.teamsPath}/${id}/edit`, { name, user }).pipe(
      map((res: Response) => {
        return res;
      })
    );
  }

  getTeamById(id: number): Observable<any> {
    return this.get(`${environment.teamsPath}/${id}`);
  }

  deleteTeam(id: number): Observable<Response> {
    return this.http.delete(`${environment.teamsPath}/${id}/delete`).pipe(
      map((res: Response) => {
        return res;
      })
    );
  }

  getTeamsDropdown(): Observable<FormDropdown[]> {
    return new Observable<FormDropdown[]>(subscriber => {
      this.getTeams().subscribe({
        next: res => {
          if (res) {
            const result: FormDropdown[] = res.items.map((item: any) => ({
              value: item.id,
              description: item.name,
            }));

            subscriber.next(result);
          } else {
            subscriber.next([]);
          }
        },
        error: err => {
          console.log(err);
          subscriber.next([]);
        },
        complete: () => subscriber.complete(),
      });
    });
  }
}
