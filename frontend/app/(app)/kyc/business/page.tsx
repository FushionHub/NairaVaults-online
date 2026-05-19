"use client"
import { useState } from "react"
import { useRouter } from "next/navigation"
import api from "@/lib/api"

export default function BusinessKycPage() {
  const router = useRouter()
  const [isLoading, setIsLoading] = useState(false)
  const [form, setForm] = useState({ business_name: "", rc_number: "", business_type: "llc", tax_id: "", address: "" })

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/kyc/business/submit", form)
      router.push("/kyc")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Business KYC</h1>
      <form onSubmit={handleSubmit} className="space-y-4">
        {(["business_name", "rc_number", "tax_id", "address"] as const).map((field) => (
          <div key={field}>
            <label className="block text-sm font-medium mb-1 capitalize">{field.replace(/_/g, " ")}</label>
            <input type="text" value={form[field]} onChange={(e) => setForm((p) => ({ ...p, [field]: e.target.value }))}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary" />
          </div>
        ))}
        <select value={form.business_type} onChange={(e) => setForm((p) => ({ ...p, business_type: e.target.value }))}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background">
          <option value="llc">LLC</option>
          <option value="sole_proprietorship">Sole Proprietorship</option>
          <option value="partnership">Partnership</option>
          <option value="corporation">Corporation</option>
        </select>
        <button type="submit" disabled={isLoading}
          className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Submitting..." : "Submit Business KYC"}
        </button>
      </form>
    </div>
  )
}
