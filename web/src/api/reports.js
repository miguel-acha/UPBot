import api from "./client";

export const getSemesters = () => api.get("/semesters");
export const getKpis      = (params) => api.get("/reports/offerings/summary", { params });

// PDFs
export const pdfOfferings = (params) => api.get("/reports/offerings.pdf",   { params, responseType: "blob" });
export const pdfEnrolls   = (params) => api.get("/reports/enrollments.pdf", { params, responseType: "blob" });
export const pdfGrades    = (params) => api.get("/reports/grades.pdf",      { params, responseType: "blob" });

export function downloadBlob(blob, filename){
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement("a");
  a.href = url; a.download = filename; a.click();
  setTimeout(()=>window.URL.revokeObjectURL(url), 5000);
}
