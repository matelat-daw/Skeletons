import { Comments } from "./comments";
import { Constellation } from "./constellation";

export interface User {
    nick: string,
    name: string,
    surname1: string,
    surname2: string,
    email: string,
    phoneNumber: string,  // Cambiado de number a string para mejor manejo
    profileImage: string,
    bday: string,
    about: string,
    userLocation: string,
    publicProfile: boolean,
    comments: Comments[],
    favorites: Constellation[],
}