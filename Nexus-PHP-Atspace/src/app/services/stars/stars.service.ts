import { Injectable } from '@angular/core';
import { Star } from '../../models/star';

@Injectable({
  providedIn: 'root'
})
export class StarsService {

  private readonly API_URL = 'https://1771-88-24-26-59.ngrok-free.app/api/Stars'

  constructor() { }

  async getAll(): Promise<Star[]> {
    const data = await fetch(this.API_URL);
    if (!data.ok) throw new Error(`Error fetching stars: ${data.status}`);
    return data.json();
  }

  async getById(id: number): Promise<Star> {
    const data = await fetch(`${this.API_URL}/${id}`);
    if (!data.ok) throw new Error(`Error fetching star ${id}: ${data.status}`);
    return data.json();
  }

}
