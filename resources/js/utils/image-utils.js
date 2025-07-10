export const resolveImgUrl = imagePath => {
  if (!imagePath) return null
  
  return `${import.meta.env.VITE_BASE_URL}/storage/${imagePath}`
}
