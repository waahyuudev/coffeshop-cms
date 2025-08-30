export const currentDate = () => {

  const date = new Date()
  const year = date.getFullYear()
  const month = (date.getMonth() + 1).toString().padStart(2, '0') // Ensure two digits
  const day = date.getDate().toString().padStart(2, '0') // Ensure two digits

  // Format the date as "YYYY-MM-DD"
  return `${year}-${month}-${day}`
}
