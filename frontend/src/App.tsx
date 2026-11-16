import { useEffect, useState } from 'react'
import type { FormEvent } from 'react'

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL ?? 'http://localhost:8000/api/v1'

type Tournament = {
  id: string
  name: string
  slug: string
  timezone: string
  start_date: string
  end_date: string
  round_count: number
  venue: string | null
  status: string
  ruleset_version: string
}

type Participant = {
  id: string
  tpn: number | null
  rating_snapshot: number | null
  title_snapshot: string
  federation_snapshot: string | null
  registration_status: string
  player: { full_name: string; fide_id: string | null; club: string | null }
}

type TournamentForm = {
  name: string
  slug: string
  venue: string
  timezone: string
  start_date: string
  end_date: string
  round_count: string
}

const initialForm: TournamentForm = {
  name: 'OpenSwiss Demo Tournament',
  slug: 'openswiss-demo-tournament',
  venue: 'Local Demo Hall',
  timezone: 'Asia/Jakarta',
  start_date: '2026-12-01',
  end_date: '2026-12-01',
  round_count: '4',
}

function App() {
  const [email, setEmail] = useState('')
  const [password, setPassword] = useState('')
  const [token, setToken] = useState<string | null>(localStorage.getItem('openswiss_token'))
  const [tournaments, setTournaments] = useState<Tournament[]>([])
  const [form, setForm] = useState<TournamentForm>(initialForm)
  const [showCreateForm, setShowCreateForm] = useState(false)
  const [error, setError] = useState<string | null>(null)
  const [notice, setNotice] = useState<string | null>(null)
  const [loading, setLoading] = useState(false)
  const [selectedTournament, setSelectedTournament] = useState<Tournament | null>(null)
  const [participants, setParticipants] = useState<Participant[]>([])
  const [participantName, setParticipantName] = useState('')
  const [participantRating, setParticipantRating] = useState('')
  const [participantTitle, setParticipantTitle] = useState('none')
  const [participantFederation, setParticipantFederation] = useState('')

  useEffect(() => {
    if (!token) return
    void loadTournaments(token)
  }, [token])

  async function loadTournaments(authToken: string) {
    const response = await fetch(`${apiBaseUrl}/tournaments`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${authToken}` },
    })
    if (!response.ok) {
      setError('Could not load tournaments.')
      return
    }

    const payload = (await response.json()) as { data: { data: Tournament[] } }
    setTournaments(payload.data.data)
  }

  async function loadParticipants(tournament: Tournament) {
    if (!token) return
    const response = await fetch(`${apiBaseUrl}/tournaments/${tournament.id}/participants`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    })
    if (!response.ok) {
      setError('Could not load participants.')
      return
    }
    const payload = (await response.json()) as { data: { data: Participant[] } }
    setSelectedTournament(tournament)
    setParticipants(payload.data.data)
  }

  async function addParticipant(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    if (!token || !selectedTournament || !participantName.trim()) return
    const response = await fetch(`${apiBaseUrl}/tournaments/${selectedTournament.id}/participants`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        full_name: participantName,
        standard_rating: participantRating ? Number(participantRating) : null,
        title: participantTitle,
        federation_code: participantFederation || null,
      }),
    })
    if (!response.ok) {
      setError('Participant could not be added.')
      return
    }
    setParticipantName('')
    setParticipantRating('')
    setParticipantFederation('')
    await loadParticipants(selectedTournament)
  }

  async function lockParticipants() {
    if (!token || !selectedTournament) return
    const response = await fetch(`${apiBaseUrl}/tournaments/${selectedTournament.id}/lock-participants`, {
      method: 'POST',
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    })
    if (!response.ok) {
      setError('Participant list could not be locked.')
      return
    }
    setNotice('Participant list locked and TPNs assigned using rating, title, and name.')
    await loadParticipants(selectedTournament)
  }

  async function signIn(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    setError(null)
    const response = await fetch(`${apiBaseUrl}/auth/login`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({ email, password }),
    })

    if (!response.ok) {
      setError('Sign-in failed. Check your credentials and try again.')
      return
    }

    const payload = (await response.json()) as { data: { token: string } }
    localStorage.setItem('openswiss_token', payload.data.token)
    setToken(payload.data.token)
  }

  async function createTournament(event: FormEvent<HTMLFormElement>) {
    event.preventDefault()
    if (!token) return
    setLoading(true)
    setError(null)
    setNotice(null)

    const response = await fetch(`${apiBaseUrl}/tournaments`, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token}`,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        ...form,
        round_count: Number(form.round_count),
        scoring_config: { win: 2, draw: 1, loss: 0 },
        tie_break_config: ['buchholz'],
        pairing_system: 'fide_dutch',
        ruleset_version: 'fide-dutch-2026-02',
        is_public: false,
      }),
    })

    setLoading(false)
    if (!response.ok) {
      const payload = (await response.json().catch(() => null)) as { message?: string } | null
      setError(payload?.message ?? 'Tournament could not be created.')
      return
    }

    setShowCreateForm(false)
    setNotice('Tournament created. Participant registration is the next workflow step.')
    await loadTournaments(token)
  }

  function signOut() {
    localStorage.removeItem('openswiss_token')
    setToken(null)
    setTournaments([])
  }

  if (!token) {
    return (
      <main className="flex min-h-screen items-center justify-center px-6">
        <form className="w-full max-w-md rounded-lg border border-slate-200 bg-white p-8 shadow-sm" onSubmit={signIn}>
          <p className="text-sm font-semibold uppercase tracking-wide text-blue-700">OpenSwiss</p>
          <h1 className="mt-2 text-3xl font-semibold">Sign in</h1>
          <p className="mt-2 text-slate-600">Access your tournament workspace.</p>
          <label className="mt-8 block text-sm font-medium" htmlFor="email">Email</label>
          <input className="mt-2 w-full rounded border border-slate-300 px-3 py-2" id="email" type="email" required value={email} onChange={(event) => setEmail(event.target.value)} />
          <label className="mt-4 block text-sm font-medium" htmlFor="password">Password</label>
          <input className="mt-2 w-full rounded border border-slate-300 px-3 py-2" id="password" type="password" required value={password} onChange={(event) => setPassword(event.target.value)} />
          {error && <p className="mt-4 text-sm text-red-700" role="alert">{error}</p>}
          <button className="mt-6 w-full rounded bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800" type="submit">Sign in</button>
        </form>
      </main>
    )
  }

  return (
    <main className="mx-auto max-w-5xl px-6 py-10">
      <header className="flex items-center justify-between border-b border-slate-200 pb-6">
        <div>
          <p className="text-sm font-semibold uppercase tracking-wide text-blue-700">OpenSwiss</p>
          <h1 className="mt-1 text-3xl font-semibold">Tournament control desk</h1>
        </div>
        <button className="rounded border border-slate-300 px-4 py-2 text-sm" onClick={signOut}>Sign out</button>
      </header>

      {notice && <p className="mt-6 rounded border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{notice}</p>}
      {error && <p className="mt-6 rounded border border-red-200 bg-red-50 p-4 text-red-800" role="alert">{error}</p>}

      <section className="mt-8 flex items-center justify-between">
        <div>
          <h2 className="text-xl font-semibold">Your tournaments</h2>
          <p className="mt-1 text-slate-600">Create a local tournament and configure its rounds.</p>
        </div>
        <button className="rounded bg-blue-700 px-4 py-2 font-medium text-white hover:bg-blue-800" onClick={() => setShowCreateForm(!showCreateForm)}>
          {showCreateForm ? 'Cancel' : 'Create tournament'}
        </button>
      </section>

      {showCreateForm && (
        <form className="mt-6 rounded-lg border border-slate-200 bg-white p-6 shadow-sm" onSubmit={createTournament}>
          <div className="grid gap-5 md:grid-cols-2">
            {([
              ['name', 'Tournament name', 'text'],
              ['slug', 'Slug', 'text'],
              ['venue', 'Venue', 'text'],
              ['timezone', 'Timezone', 'text'],
              ['start_date', 'Start date', 'date'],
              ['end_date', 'End date', 'date'],
              ['round_count', 'Number of rounds', 'number'],
            ] as const).map(([field, label, type]) => (
              <label className="block text-sm font-medium" htmlFor={field} key={field}>
                {label}
                <input
                  className="mt-2 w-full rounded border border-slate-300 px-3 py-2 font-normal"
                  id={field}
                  min={field === 'round_count' ? 1 : undefined}
                  required={field !== 'venue'}
                  type={type}
                  value={form[field]}
                  onChange={(event) => setForm({ ...form, [field]: event.target.value })}
                />
              </label>
            ))}
          </div>
          <div className="mt-6 rounded border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
            Demo target: <strong>4 rounds</strong> and 20 participants. This screen creates the
            tournament; participant registration/import will be added in the next workflow.
          </div>
          <button className="mt-6 rounded bg-blue-700 px-4 py-2 font-medium text-white disabled:opacity-50" disabled={loading} type="submit">
            {loading ? 'Creating...' : 'Create tournament'}
          </button>
        </form>
      )}

      <section className="mt-6 space-y-3">
        {tournaments.length === 0 && <div className="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center text-slate-600">No tournaments yet.</div>}
        {tournaments.map((tournament) => (
          <article className="rounded-lg border border-slate-200 bg-white p-5 shadow-sm" key={tournament.id} onClick={() => void loadParticipants(tournament)}>
            <div className="flex flex-wrap items-center justify-between gap-3">
              <div>
                <h3 className="font-semibold">{tournament.name}</h3>
                <p className="text-sm text-slate-600">{tournament.venue ?? 'No venue'} · {tournament.timezone}</p>
              </div>
              <span className="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium uppercase">{tournament.status}</span>
            </div>
            <p className="mt-3 text-sm text-slate-700">{tournament.round_count} rounds · {tournament.ruleset_version}</p>
          </article>
        ))}
      </section>

      {selectedTournament && (
        <section className="mt-8 rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <div>
              <h2 className="text-xl font-semibold">{selectedTournament.name} participants</h2>
              <p className="mt-1 text-sm text-slate-600">{participants.length} registered · click Lock when the initial list is complete</p>
            </div>
            <button className="rounded bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700" onClick={() => void lockParticipants()}>
              Lock participant list
            </button>
          </div>
          <form className="mt-6 grid gap-3 md:grid-cols-5" onSubmit={addParticipant}>
            <input className="rounded border border-slate-300 px-3 py-2 md:col-span-2" placeholder="Full name" required value={participantName} onChange={(event) => setParticipantName(event.target.value)} />
            <input className="rounded border border-slate-300 px-3 py-2" placeholder="Standard rating" type="number" min="0" max="4000" value={participantRating} onChange={(event) => setParticipantRating(event.target.value)} />
            <select className="rounded border border-slate-300 px-3 py-2" value={participantTitle} onChange={(event) => setParticipantTitle(event.target.value)}>
              {['none', 'GM', 'IM', 'FM', 'CM', 'WGM', 'WIM', 'WFM', 'WCM'].map((title) => <option key={title} value={title}>{title}</option>)}
            </select>
            <input className="rounded border border-slate-300 px-3 py-2" placeholder="Federation" maxLength={3} value={participantFederation} onChange={(event) => setParticipantFederation(event.target.value.toUpperCase())} />
            <button className="rounded bg-blue-700 px-4 py-2 font-medium text-white md:col-span-5 md:justify-self-start" type="submit">Add participant</button>
          </form>
          <div className="mt-6 overflow-x-auto">
            <table className="w-full text-left text-sm">
              <thead><tr className="border-b border-slate-200 text-slate-600"><th className="px-3 py-2">TPN</th><th className="px-3 py-2">Name</th><th className="px-3 py-2">Title</th><th className="px-3 py-2">Rating</th><th className="px-3 py-2">Federation</th><th className="px-3 py-2">Status</th></tr></thead>
              <tbody>{participants.map((participant) => <tr className="border-b border-slate-100" key={participant.id}><td className="px-3 py-2">{participant.tpn ?? '—'}</td><td className="px-3 py-2 font-medium">{participant.player.full_name}</td><td className="px-3 py-2">{participant.title_snapshot}</td><td className="px-3 py-2">{participant.rating_snapshot ?? '—'}</td><td className="px-3 py-2">{participant.federation_snapshot ?? '—'}</td><td className="px-3 py-2">{participant.registration_status}</td></tr>)}</tbody>
            </table>
          </div>
        </section>
      )}
    </main>
  )
}

export default App
