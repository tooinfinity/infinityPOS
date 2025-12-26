# Frontend Development Guidelines

## Overview

This document covers frontend-specific patterns for the POS & Inventory Management System built with **React 19 + Inertia.js v2 + TypeScript + Tailwind CSS v4**.

---

## Project Structure

```
resources/js/
├── app.tsx                 # Client-side entry
├── ssr.tsx                 # SSR entry
├── pages/                  # Inertia pages (lowercase folders)
│   ├── dashboard.tsx
│   ├── pos/               # POS pages
│   ├── product/           # Product CRUD
│   ├── category/
│   ├── brand/
│   ├── tax/
│   ├── unit/
│   ├── user/
│   ├── settings/          # Settings pages
│   └── ...
├── components/            # Reusable components
│   ├── ui/               # ShadCN UI primitives (DO NOT MODIFY)
│   ├── pos/              # POS-specific components
│   ├── settings/         # Settings components
│   ├── app-*.tsx         # App shell components
│   ├── can.tsx           # Permission component
│   └── ...
├── layouts/              # Page layouts
│   ├── app-layout.tsx
│   ├── auth-layout.tsx
│   └── settings/
├── hooks/                # Custom React hooks
│   ├── use-pos-cart.ts
│   ├── use-barcode-scanner.ts
│   ├── use-permissions.ts
│   └── ...
├── types/                # TypeScript definitions
│   ├── index.d.ts        # Global types
│   ├── pos.ts           # POS types
│   ├── settings.ts      # Settings types
│   └── vite-env.d.ts
├── lib/                  # Utilities
│   └── utils.ts         # Tailwind merge, clsx
└── services/            # API services (if needed)
```

---

## Routing with Wayfinder

### Import Pattern (Tree-Shakable)

Always use named imports to enable tree-shaking:

```typescript
// ✅ CORRECT - Tree-shakable
import { index, store, update, destroy } from '@/actions/App/Http/Controllers/ProductController';

// ❌ WRONG - Prevents tree-shaking
import ProductController from '@/actions/App/Http/Controllers/ProductController';
```

### Usage Examples

```typescript
import { index, store } from '@/actions/App/Http/Controllers/ProductController';

// Get route object
index(); // { url: "/products", method: "get" }

// Get just URL
index.url(); // "/products"

// With parameters
show(1); // { url: "/products/1", method: "get" }

// With query params
index({ query: { page: 2, search: 'laptop' } });
// { url: "/products?page=2&search=laptop", method: "get" }

// Merge with current query
index({ mergeQuery: { page: 2 } });
// Merges with window.location.search

// Named routes (if needed)
import { show as productShow } from '@/routes/product';
productShow(1);
```

### With Inertia Forms

```tsx
import { Form } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/ProductController';

<Form {...store.form()}>
  <input name="name" />
  <button type="submit">Create</button>
</Form>
```

### With Inertia Links

```tsx
import { Link } from '@inertiajs/react';
import { index } from '@/actions/App/Http/Controllers/ProductController';

<Link href={index.url()}>View Products</Link>
```

---

## Form Patterns

### Approach 1: Inertia `<Form>` Component (Recommended)

Best for simple forms with straightforward validation:

```tsx
import { Form } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/ProductController';

export default function CreateProduct() {
  return (
    <Form {...store.form()}>
      {({ errors, processing, wasSuccessful }) => (
        <>
          <div>
            <label htmlFor="name">Name</label>
            <input 
              id="name" 
              name="name" 
              type="text" 
              required 
            />
            {errors.name && <span className="text-red-500">{errors.name}</span>}
          </div>

          <button type="submit" disabled={processing}>
            {processing ? 'Creating...' : 'Create Product'}
          </button>

          {wasSuccessful && (
            <div className="text-green-500">Product created!</div>
          )}
        </>
      )}
    </Form>
  );
}
```

### Approach 2: `useForm` Hook

Best for complex forms needing programmatic control:

```tsx
import { useForm } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/ProductController';

export default function CreateProduct() {
  const { data, setData, post, processing, errors, reset } = useForm({
    name: '',
    price: 0,
    category_id: null,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    post(store.url(), {
      onSuccess: () => {
        reset();
        // Additional logic
      },
    });
  };

  return (
    <form onSubmit={handleSubmit}>
      <input
        value={data.name}
        onChange={(e) => setData('name', e.target.value)}
      />
      {errors.name && <span>{errors.name}</span>}
      
      <button type="submit" disabled={processing}>
        Submit
      </button>
    </form>
  );
}
```

### Form Validation Display

```tsx
// Inline errors
{errors.field && (
  <p className="mt-1 text-sm text-red-600">{errors.field}</p>
)}

// Use InputError component (if available)
import InputError from '@/components/input-error';

<InputError message={errors.field} className="mt-2" />
```

---

## Page Component Pattern

### Standard Page Structure

```tsx
import { Head } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import Heading from '@/components/heading';

interface PageProps {
  products: Array<{
    id: number;
    name: string;
    price: number;
  }>;
  // Type all props from controller
}

export default function ProductIndex({ products }: PageProps) {
  return (
    <>
      <Head title="Products" />
      
      <Heading>Products</Heading>
      
      <div className="space-y-4">
        {products.map((product) => (
          <div key={product.id}>
            {product.name} - ${product.price}
          </div>
        ))}
      </div>
    </>
  );
}

// Assign layout
ProductIndex.layout = (page: React.ReactNode) => (
  <AppLayout>{page}</AppLayout>
);
```

### Props Type Safety

**CRITICAL:** Props must match backend controller exactly.

```typescript
// Backend (Controller):
return Inertia::render('Products/Index', [
    'products' => ProductData::collection($products),
    'categories' => CategoryData::collection($categories),
]);

// Frontend (Page):
interface ProductIndexProps {
  products: Array<{
    id: number;
    name: string;
    price: number;
    category?: {
      id: number;
      name: string;
    };
  }>;
  categories: Array<{
    id: number;
    name: string;
  }>;
}
```

### Shared Data

Available on all pages via `usePage`:

```typescript
import { usePage } from '@inertiajs/react';

interface SharedProps {
  auth: {
    user: {
      id: number;
      name: string;
      email: string;
      permissions: string[];
    };
  };
  flash: {
    success?: string;
    error?: string;
  };
}

const { props } = usePage<SharedProps>();
const user = props.auth.user;
```

---

## Component Patterns

### UI Components (ShadCN)

Located in `resources/js/components/ui/`. **DO NOT MODIFY THESE.**

```tsx
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Card, CardHeader, CardTitle, CardContent } from '@/components/ui/card';

// Use as documented in ShadCN
<Button variant="default" size="lg">Click Me</Button>
```

### Permission-Based Components

```tsx
import { Can } from '@/components/can';

// Show only if user has permission
<Can permission="products_create">
  <Button>Create Product</Button>
</Can>

// Multiple permissions (any)
<Can permission={['products_update', 'products_delete']}>
  <Button>Actions</Button>
</Can>
```

### Loading States

Use skeleton loaders, not spinners:

```tsx
import { Skeleton } from '@/components/ui/skeleton';

function ProductCardSkeleton() {
  return (
    <Card>
      <CardHeader>
        <Skeleton className="h-4 w-[250px]" />
      </CardHeader>
      <CardContent>
        <Skeleton className="h-20 w-full" />
      </CardContent>
    </Card>
  );
}

// Usage with loading state
{loading ? (
  <ProductCardSkeleton />
) : (
  <ProductCard product={product} />
)}
```

### Empty States

Always provide empty states:

```tsx
function EmptyState({ message, action }: { message: string; action?: React.ReactNode }) {
  return (
    <div className="flex flex-col items-center justify-center py-12 text-center">
      <p className="text-muted-foreground">{message}</p>
      {action && <div className="mt-4">{action}</div>}
    </div>
  );
}

// Usage
{products.length === 0 ? (
  <EmptyState 
    message="No products found" 
    action={
      <Can permission="products_create">
        <Button>Create Product</Button>
      </Can>
    }
  />
) : (
  <ProductList products={products} />
)}
```

---

## Custom Hooks

### Permission Hook

```typescript
import { usePermissions } from '@/hooks/use-permissions';

function MyComponent() {
  const { can, hasRole } = usePermissions();

  if (!can('products_view')) {
    return <AccessDenied />;
  }

  return (
    <>
      {can('products_create') && <CreateButton />}
      {hasRole('admin') && <AdminPanel />}
    </>
  );
}
```

### Debounce Hook

```typescript
import { useDebounce } from '@/hooks/use-debounce';

function SearchProducts() {
  const [search, setSearch] = useState('');
  const debouncedSearch = useDebounce(search, 500);

  useEffect(() => {
    if (debouncedSearch) {
      // Perform search
    }
  }, [debouncedSearch]);

  return (
    <input 
      value={search} 
      onChange={(e) => setSearch(e.target.value)} 
    />
  );
}
```

### POS Cart Hook

```typescript
import { usePosCart } from '@/hooks/use-pos-cart';

function POSInterface() {
  const {
    cart,
    addItem,
    removeItem,
    updateQuantity,
    applyDiscount,
    clearCart,
    totals,
  } = usePosCart();

  return (
    <>
      {cart.items.map((item) => (
        <div key={item.product_id}>
          {item.name} x {item.quantity}
          <button onClick={() => removeItem(item.product_id)}>Remove</button>
        </div>
      ))}
      <div>Total: ${totals.total}</div>
    </>
  );
}
```

---

## Styling with Tailwind CSS v4

### Basic Patterns

```tsx
// Spacing with gap (not margins)
<div className="flex gap-4">
  <div>Item 1</div>
  <div>Item 2</div>
</div>

// Responsive design
<div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
  {items.map(item => <Card key={item.id} />)}
</div>

// Dark mode support
<div className="bg-white dark:bg-gray-800 text-black dark:text-white">
  Content
</div>
```

### Tailwind v4 Changes

**Import in CSS:**
```css
/* resources/css/app.css */
@import "tailwindcss";

@theme {
  --color-brand: oklch(0.72 0.11 178);
}
```

**Use updated utilities:**
```tsx
// ❌ OLD (v3)
<div className="bg-opacity-50">

// ✅ NEW (v4)
<div className="bg-black/50">
```

### Component Styling Pattern

```tsx
import { cn } from '@/lib/utils'; // Tailwind merge utility

interface ButtonProps {
  variant?: 'default' | 'destructive' | 'outline';
  size?: 'sm' | 'md' | 'lg';
  className?: string;
}

function Button({ variant = 'default', size = 'md', className, ...props }: ButtonProps) {
  return (
    <button
      className={cn(
        'rounded-md font-medium transition-colors',
        {
          'bg-primary text-primary-foreground': variant === 'default',
          'bg-destructive text-destructive-foreground': variant === 'destructive',
          'border border-input': variant === 'outline',
        },
        {
          'px-3 py-1.5 text-sm': size === 'sm',
          'px-4 py-2 text-base': size === 'md',
          'px-6 py-3 text-lg': size === 'lg',
        },
        className
      )}
      {...props}
    />
  );
}
```

---

## State Management

### Local Component State

For simple UI state:

```typescript
const [isOpen, setIsOpen] = useState(false);
const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
```

### Inertia State (Server State)

For data from backend:

```typescript
import { router } from '@inertiajs/react';

// Update page data without full reload
router.reload({ only: ['products'] });

// Visit with preserved scroll
router.visit(url, { preserveScroll: true });

// Replace history
router.visit(url, { replace: true });
```

### Session Storage (Rare)

Only for client-side preferences:

```typescript
// Theme, layout preferences, etc.
localStorage.setItem('sidebar-collapsed', 'true');

// ⚠️ NEVER store auth tokens or sensitive data
```

---

## Navigation Patterns

### Programmatic Navigation

```typescript
import { router } from '@inertiajs/react';
import { show } from '@/actions/App/Http/Controllers/ProductController';

// Navigate to route
router.visit(show.url(productId));

// With options
router.visit(show.url(productId), {
  preserveScroll: true,
  preserveState: true,
  onSuccess: () => {
    // After navigation
  },
});
```

### Link Component

```tsx
import { Link } from '@inertiajs/react';
import { show } from '@/actions/App/Http/Controllers/ProductController';

<Link 
  href={show.url(productId)}
  className="text-primary hover:underline"
>
  View Product
</Link>

// With preserve state
<Link 
  href={url} 
  preserveScroll 
  preserveState
>
  Next Page
</Link>
```

### Back Navigation

```typescript
import { router } from '@inertiajs/react';

<button onClick={() => router.visit(history.back())}>
  Back
</button>
```

---

## Error Handling

### Form Errors

```tsx
import { useForm } from '@inertiajs/react';

const { errors, setError, clearErrors } = useForm();

// Display error
{errors.name && <span>{errors.name}</span>}

// Clear specific error
clearErrors('name');

// Clear all errors
clearErrors();
```

### Global Error Handling

```tsx
import { router } from '@inertiajs/react';

router.on('error', (event) => {
  // Handle network errors
  console.error('Navigation error:', event.detail);
});
```

### API Error Display

```tsx
import { toast } from 'sonner';

const handleAction = async () => {
  try {
    await someApiCall();
    toast.success('Action completed');
  } catch (error) {
    toast.error('Something went wrong');
  }
};
```

---

## Notifications & Feedback

### Toast Notifications (Sonner)

```typescript
import { toast } from 'sonner';

// Success
toast.success('Product created successfully');

// Error
toast.error('Failed to delete product');

// Info
toast.info('Product updated');

// Loading
const toastId = toast.loading('Creating product...');
// Later:
toast.success('Product created', { id: toastId });

// Custom
toast.custom((t) => (
  <div>Custom notification content</div>
));
```

### Flash Messages

```typescript
import { usePage } from '@inertiajs/react';

const { flash } = usePage().props;

useEffect(() => {
  if (flash.success) {
    toast.success(flash.success);
  }
  if (flash.error) {
    toast.error(flash.error);
  }
}, [flash]);
```

---

## Accessibility

### Keyboard Navigation

```tsx
// Tab order
<form>
  <input tabIndex={1} />
  <input tabIndex={2} />
  <button tabIndex={3}>Submit</button>
</form>

// Skip to content
<a href="#main-content" className="sr-only focus:not-sr-only">
  Skip to content
</a>
```

### ARIA Labels

```tsx
<button aria-label="Close dialog" onClick={onClose}>
  <X className="h-4 w-4" />
</button>

<input 
  aria-describedby="email-help"
  aria-invalid={!!errors.email}
/>
{errors.email && (
  <span id="email-help" role="alert">
    {errors.email}
  </span>
)}
```

### Focus Management

```tsx
import { useEffect, useRef } from 'react';

function Modal({ isOpen }: { isOpen: boolean }) {
  const inputRef = useRef<HTMLInputElement>(null);

  useEffect(() => {
    if (isOpen) {
      inputRef.current?.focus();
    }
  }, [isOpen]);

  return (
    <div>
      <input ref={inputRef} />
    </div>
  );
}
```

---

## Performance Optimization

### React.memo

```tsx
import { memo } from 'react';

const ProductCard = memo(({ product }: { product: Product }) => {
  return (
    <Card>
      <CardHeader>{product.name}</CardHeader>
    </Card>
  );
});
```

### useCallback

```tsx
import { useCallback } from 'react';

const handleDelete = useCallback((id: number) => {
  router.delete(destroy.url(id));
}, []);
```

### useMemo

```tsx
import { useMemo } from 'react';

const filteredProducts = useMemo(() => {
  return products.filter(p => p.name.includes(search));
}, [products, search]);
```

### Code Splitting

```tsx
import { lazy, Suspense } from 'react';

const HeavyComponent = lazy(() => import('./heavy-component'));

<Suspense fallback={<Skeleton />}>
  <HeavyComponent />
</Suspense>
```

---

## TypeScript Best Practices

### No `any`

```typescript
// ❌ WRONG
const data: any = response.data;

// ✅ CORRECT
interface ResponseData {
  products: Product[];
}
const data: ResponseData = response.data;

// If truly unknown
const data: unknown = response.data;
if (isProduct(data)) {
  // Type narrowed
}
```

### Type Inference

```typescript
// Let TypeScript infer when obvious
const [count, setCount] = useState(0); // Inferred as number

// Explicit when needed
const [product, setProduct] = useState<Product | null>(null);
```

### Props Interfaces

```typescript
interface ProductCardProps {
  product: Product;
  onDelete?: (id: number) => void;
  className?: string;
}

export function ProductCard({ 
  product, 
  onDelete, 
  className 
}: ProductCardProps) {
  // ...
}
```

---

## Testing Frontend

### Component Testing

```typescript
import { render, screen } from '@testing-library/react';
import ProductCard from '@/components/product-card';

test('displays product name', () => {
  const product = { id: 1, name: 'Laptop', price: 999 };
  render(<ProductCard product={product} />);
  
  expect(screen.getByText('Laptop')).toBeInTheDocument();
});
```

### Browser Testing (Pest v4)

```php
it('can create a product via UI', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    
    $page = visit('/products/create');
    
    $page->fill('name', 'Test Product')
        ->fill('price', '99.99')
        ->press('Create Product')
        ->assertSee('Product created successfully');
});
```

---

## Common Pitfalls

### ❌ Don't Modify Backend Data in Frontend

```tsx
// ❌ WRONG - Mutating server data
const updateStock = (productId: number) => {
  products.find(p => p.id === productId)!.stock += 1;
};

// ✅ CORRECT - Request backend update
const updateStock = (productId: number) => {
  router.post(updateStockUrl, { product_id: productId });
};
```

### ❌ Don't Hard-Code URLs

```tsx
// ❌ WRONG
<Link href="/products/1">View</Link>

// ✅ CORRECT
import { show } from '@/actions/App/Http/Controllers/ProductController';
<Link href={show.url(1)}>View</Link>
```

### ❌ Don't Store Sensitive Data Client-Side

```tsx
// ❌ WRONG
localStorage.setItem('auth_token', token);

// ✅ CORRECT - Let Laravel handle sessions
```

### ❌ Don't Implement Business Logic in Frontend

```tsx
// ❌ WRONG - Complex calculations in frontend
const calculateDiscount = (price: number, customer: Customer) => {
  if (customer.tier === 'gold' && price > 100) {
    return price * 0.15;
  }
  // ...complex logic
};

// ✅ CORRECT - Backend calculates, frontend displays
<div>Discount: ${sale.discount_amount}</div>
```

---

## Quick Reference

### Essential Imports

```typescript
// Inertia
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';

// React
import { useState, useEffect, useCallback, useMemo, memo } from 'react';

// UI Components
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardContent, CardHeader } from '@/components/ui/card';

// Custom
import { Can } from '@/components/can';
import { usePermissions } from '@/hooks/use-permissions';
import { cn } from '@/lib/utils';

// Notifications
import { toast } from 'sonner';
```

### Common Code Snippets

**Page with Form:**
```tsx
import { Head, useForm } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/ProductController';

export default function Create() {
  const { data, setData, post, processing, errors } = useForm({
    name: '',
  });

  return (
    <>
      <Head title="Create Product" />
      <form onSubmit={(e) => { e.preventDefault(); post(store.url()); }}>
        <input value={data.name} onChange={e => setData('name', e.target.value)} />
        {errors.name && <span>{errors.name}</span>}
        <button disabled={processing}>Create</button>
      </form>
    </>
  );
}
```

**Data Table:**
```tsx
import { Link } from '@inertiajs/react';
import { index } from '@/actions/App/Http/Controllers/ProductController';

export default function Index({ products }: { products: Product[] }) {
  return (
    <table>
      <thead>
        <tr><th>Name</th><th>Price</th></tr>
      </thead>
      <tbody>
        {products.map(p => (
          <tr key={p.id}>
            <td><Link href={index.url()}>{p.name}</Link></td>
            <td>${p.price}</td>
          </tr>
        ))}
      </tbody>
    </table>
  );
}
```

---

**Remember:** Always verify controller props before using them. Never guess the data shape!
