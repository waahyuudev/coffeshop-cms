export function formatOrderDate(dateString) {
  if (!dateString) return '-'

  const options = {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    hour12: false,
  }

  return new Date(dateString).toLocaleString("id-ID", options)
}
