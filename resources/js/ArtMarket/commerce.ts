export type MoneyValue = number | string;

export type CardImage = {
    src: string;
    alt: string;
    width: number;
    height: number;
};

export type CategorySummary = {
    id: number;
    name: string;
    slug: string;
};

export type SellerSummary = {
    id?: number;
    store_name: string;
    slug: string;
    location?: string | null;
    rating_average?: number;
    rating_count?: number;
};

export type ProductSummary = {
    id: number;
    slug: string;
    title: string;
    excerpt?: string | null;
    description?: string | null;
    price: MoneyValue;
    stock: number;
    product_type?: string | null;
    material?: string | null;
    dimensions?: string | null;
    location?: string | null;
    rating_average?: number;
    rating_count?: number;
    image: CardImage;
    category?: CategorySummary | null;
    seller?: SellerSummary | null;
};

export type ProductReview = {
    id: number;
    rating: number;
    title?: string | null;
    body?: string | null;
    user?: {
        name: string;
    } | null;
};

export type CartItem = {
    product: ProductSummary;
    quantity: number;
    line_total: MoneyValue;
    stock_state: "available" | "insufficient" | "unavailable";
};

export type CartSummary = {
    items: CartItem[];
    total_items: number;
    subtotal: MoneyValue;
    currency: string;
    has_stock_issue: boolean;
};

export type OrderPayment = {
    invoice: string;
    gateway: string;
    gateway_reference?: string | null;
    status: string;
    amount: MoneyValue;
    redirect_url?: string | null;
    message?: string | null;
};

export const moneyNumber = (value: MoneyValue) => {
    const amount = typeof value === "number" ? value : Number.parseFloat(value);

    return Number.isFinite(amount) ? amount : 0;
};

export const formatCurrency = (value: MoneyValue) =>
    `Rp ${moneyNumber(value).toLocaleString("id-ID", { maximumFractionDigits: 0 })}`;
