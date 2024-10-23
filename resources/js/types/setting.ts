export interface Setting {
    id: BigInteger,
    uuid: string,
    app: string;
    telepon: string;
    description: string;
    alamat: string;
    email: string;
    logo: Array<File | string> | string;
    banner: Array<File | string> | string;
}