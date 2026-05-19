"use client"
import { useState } from "react"
import { useRouter } from "next/navigation"
import api from "@/lib/api"

export default function IndividualKycPage() {
  const router = useRouter()
  const [isLoading, setIsLoading] = useState(false)
  const [formData, setFormData] = useState({ bvn: "", nin: "", id_type: "national_id", id_number: "", dob: "" })

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    try {
      await api.post("/kyc/submit", formData)
      router.push("/kyc")
    } finally {
      setIsLoading(false)
    }
  }

  return (
    <div className="max-w-lg mx-auto space-y-6">
      <h1 className="text-2xl font-bold">Individual KYC</h1>
      <form onSubmit={handleSubmit} className="space-y-4">
        {["bvn", "nin", "id_number", "dob"].map((field) => (
          <div key={field}>
            <label className="block text-sm font-medium mb-1 capitalize">{field.replace("_", " ")}</label>
            <input
              type={field === "dob" ? "date" : "text"}
              value={formData[field as keyof typeof formData]}
              onChange={(e) => setFormData((p) => ({ ...p, [field]: e.target.value }))}
              className="w-full px-4 py-2 rounded-lg border border-input bg-background outline-none focus:ring-2 focus:ring-primary"
            />
          </div>
        ))}
        <select
          value={formData.id_type}
          onChange={(e) => setFormData((p) => ({ ...p, id_type: e.target.value }))}
          className="w-full px-4 py-2 rounded-lg border border-input bg-background"
        >
          <option value="national_id">National ID</option>
          <option value="passport">Passport</option>
          <option value="drivers_license">Driver&apos;s License</option>
        </select>
        <button type="submit" disabled={isLoading} className="w-full py-3 rounded-lg bg-primary text-primary-foreground font-medium disabled:opacity-50">
          {isLoading ? "Submitting..." : "Submit KYC"}
        </button>
      </form>
    </div>
  )
}
