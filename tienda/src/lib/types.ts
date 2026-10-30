export type Category = {
  id: number;
  name: string;
};

export type Product = {
  id: number;
  name: string;
  price: number | string;
  stock: number;
  is_featured: boolean;
  category: Category | null;
  description?: string;
  image?: string;
};

export type ApiList<T> = { data: T[] };
export type ApiItem<T> = { data: T };