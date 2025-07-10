import products from './product-nav'
import orders from "./order-nav"

export default [
  {
    title: 'Dashboard',
    to: { name: 'root' },
    icon: { icon: 'tabler-smart-home' },
  },
  ...products,
  ...orders,
]
