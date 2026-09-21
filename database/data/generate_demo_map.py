#!/usr/bin/env python3
"""
Generates demo-hotel-map.json - the demo hotel used until a real map is loaded.

Layout (every floor is a 1000 x 600 coordinate space, 1 unit = 0.1 m):
  - one E-W corridor at y=300, with a corridor node at the centre of each of 8 "slots"
  - rooms/areas north (y 40-270) and south (y 330-560) of the corridor
  - elevator = north side of slot 3, stairs = south side of slot 4, on EVERY floor
Run:  python3 generate_demo_map.py   (rewrites demo-hotel-map.json next to it)
"""
import json, math, os
from collections import deque

W, H = 1000, 600
CORR_Y = 300
SLOT_W = 110
X0 = 60
def sx(i): return X0 + i * SLOT_W            # left edge of slot i
def cx(i): return sx(i) + SLOT_W // 2         # corridor node x for slot i (also door x)

FLOORS = [("B1", "Basement", -1), ("G", "Ground Floor", 0), ("1", "1st Floor", 1), ("2", "2nd Floor", 2), ("3", "3rd Floor", 3)]

floors, nodes, locations = [], [], []

def fid(label): return "f" + label.lower()

def area(fl, aid, kind, slots, side, label=None):
    """Rect covering slots[0]..slots[-1] on the north or south side of the corridor."""
    x = sx(slots[0]) + 3
    w = (slots[-1] - slots[0] + 1) * SLOT_W - 6
    y, h = (43, 224) if side == "n" else (333, 224)
    a = {"id": f"{fid(fl)}-{aid}", "kind": kind, "x": x, "y": y, "w": w, "h": h}
    if label: a["label"] = label
    return a

def add_floor(label, name, level):
    f = {"id": fid(label), "label": label, "name": name, "level": level, "width": W, "height": H, "plan_image": None,
         "areas": [
             {"id": f"{fid(label)}-building", "kind": "building", "x": 56, "y": 36, "w": 888, "h": 528},
             {"id": f"{fid(label)}-corridor", "kind": "corridor", "x": 56, "y": 270, "w": 888, "h": 60},
         ]}
    floors.append(f)
    # corridor nodes c0..c7
    for i in range(8):
        n = {"id": f"{fid(label)}-c{i}", "floor": fid(label), "x": cx(i), "y": CORR_Y, "type": "walk", "connections": []}
        nodes.append(n)
    ns = [n for n in nodes if n["floor"] == fid(label)]
    for a, b in zip(ns, ns[1:]):
        a["connections"].append(b["id"]); b["connections"].append(a["id"])
    # lift + stairs
    for n in ns:
        if n["id"].endswith("-c3"): n["type"] = "elevator"; n["door"] = {"x": cx(3), "y": 248}
        if n["id"].endswith("-c4"): n["type"] = "stairs"; n["door"] = {"x": cx(4), "y": 352}
    f["areas"] += [area(label, "elev", "service", [3], "n", "Elevators"), area(label, "stairs", "service", [4], "s", "Stairs")]
    return f

def loc(fl, lid, name, category, x, y, node_slot, desc="", hours=None, ref=None, image=None):
    l = {"id": lid, "name": name, "description": desc, "image": image, "floor": fid(fl), "x": x, "y": y,
         "category": category, "opening_hours": hours, "node": f"{fid(fl)}-c{node_slot}"}
    if ref: l["ref"] = ref
    locations.append(l)

def place(fl, lid, name, category, slots, side, kind, node_slot=None, label=None, **kw):
    """Add an area + a location centred in it, with its door on the corridor node of `node_slot`."""
    a = area(fl, lid, kind, slots, side, label or name)
    floor = next(f for f in floors if f["id"] == fid(fl))
    floor["areas"].append(a)
    slot = node_slot if node_slot is not None else slots[len(slots) // 2 if len(slots) % 2 else len(slots) // 2 - 1]
    loc(fl, lid, name, category, a["x"] + a["w"] // 2, a["y"] + a["h"] // 2 + (0 if side == "n" else 0), slot, **kw)

def room(fl, number, slot, side, big=None):
    place(fl, f"room-{number}", f"Room {number}", "room", [slot], side, "room", label=str(number),
          desc=f"A comfortable guest room on the {dict(FLOORS_BY_LABEL)[fl]}.")

FLOORS_BY_LABEL = {l: n for l, n, _ in FLOORS}

def core_locations(fl):
    f = next(f for f in floors if f["id"] == fid(fl))
    loc(fl, f"elevator-{fl.lower()}", "Elevators", "transport", cx(3), 250, 3, desc="Lifts to all floors.")
    loc(fl, f"stairs-{fl.lower()}", "Stairs", "transport", cx(4), 400, 4, desc="Staircase to all floors.")

for label, name, level in FLOORS:
    add_floor(label, name, level)
    core_locations(label)

# ---------------- B1: wellness level ----------------
place("B1", "pool", "Infinity Pool", "pool", [0, 1, 2], "n", "water", node_slot=1,
      desc="Heated indoor infinity pool with loungers and a poolside bar.", hours="07:00 - 21:00",
      ref={"type": "facility", "slug": "infinity-pool"})
place("B1", "spa", "Serenity Spa", "wellness", [5, 6, 7], "n", "public", node_slot=6,
      desc="Massages, facials and treatments in a calm, candle-lit setting.", hours="09:00 - 21:00",
      ref={"type": "facility", "slug": "serenity-spa"})
place("B1", "gym", "Fitness Center", "fitness", [0, 1], "s", "public", node_slot=0,
      desc="Cardio, free weights and a stretching studio.", hours="24 hours",
      ref={"type": "facility", "slug": "fitness-center"})
place("B1", "restrooms-b1", "Restrooms", "restroom", [2], "s", "service", desc="Restrooms.")
place("B1", "sauna", "Sauna & Steam", "wellness", [5], "s", "public", desc="Finnish sauna and steam room.", hours="09:00 - 21:00")
place("B1", "juice-bar", "Juice Bar", "cafe", [6, 7], "s", "public", node_slot=6, desc="Fresh juices, smoothies and light bites after your workout.", hours="08:00 - 20:00")

# ---------------- G: arrival level ----------------
ns_g = [n for n in nodes if n["floor"] == fid("G")]
entrance = {"id": "fg-e0", "floor": fid("G"), "x": 66, "y": CORR_Y, "type": "entrance", "connections": ["fg-c0"]}
nodes.append(entrance); next(n for n in nodes if n["id"] == "fg-c0")["connections"].append("fg-e0")
loc("G", "entrance", "Main Entrance", "entrance", 66, CORR_Y, 0, desc="Welcome to Grand Horizon.")
locations[-1]["node"] = "fg-e0"
place("G", "reception", "Reception", "reception", [0, 1], "n", "public", node_slot=0, desc="Check-in, check-out and any help you need, day or night.", hours="24 hours")
place("G", "concierge", "Concierge", "facility", [2], "n", "public", desc="Tours, transport and restaurant bookings.", hours="07:00 - 22:00")
place("G", "restrooms-g", "Restrooms", "restroom", [4], "n", "service", desc="Restrooms.")
place("G", "cafe-aroma", "Cafe Aroma", "cafe", [5, 6], "n", "public", node_slot=5, desc="Specialty coffee, pastries and light lunches.", hours="06:30 - 22:00")
place("G", "gift-shop", "Gift Shop", "facility", [7], "n", "public", desc="Souvenirs, essentials and local crafts.", hours="09:00 - 21:00")
place("G", "lobby", "Lobby & Lounge", "lobby", [0, 1, 2], "s", "public", node_slot=1, desc="Relax in the lounge with a drink and live piano in the evenings.")
place("G", "business-center", "Business Center", "facility", [3], "s", "public", desc="Printing, meeting pods and fast Wi-Fi.", hours="07:00 - 22:00",
      ref={"type": "facility", "slug": "business-center"})
place("G", "azure", "Azure Restaurant", "dining", [5, 6, 7], "s", "public", node_slot=6, desc="Mediterranean cuisine with sea views.", hours="12:00 - 23:00",
      ref={"type": "restaurant", "slug": "azure-restaurant"})

# ---------------- 1: rooms + meeting ----------------
for n_, s_ in zip([101, 102, 103], [0, 1, 2]): room("1", n_, s_, "n")
for n_, s_ in zip([104, 105, 106, 107], [4, 5, 6, 7]): room("1", n_, s_, "n")
for n_, s_ in zip([108, 109], [0, 1]): room("1", n_, s_, "s")
place("1", "restrooms-1", "Restrooms", "restroom", [2], "s", "service", desc="Restrooms.")
place("1", "meeting-a", "Meeting Room A", "meeting", [5], "s", "public", desc="Bright room for up to 12 guests with a projector and video conferencing.", hours="08:00 - 20:00")
place("1", "meeting-b", "Meeting Room B", "meeting", [6], "s", "public", desc="Seats 8 around a boardroom table, with whiteboard and screen.", hours="08:00 - 20:00")
place("1", "boardroom", "Boardroom", "meeting", [7], "s", "public", desc="Executive boardroom for up to 16, with catering on request.", hours="08:00 - 20:00")

# ---------------- 2: dining + rooms ----------------
place("2", "garden", "The Garden Restaurant", "dining", [0, 1, 2], "n", "public", node_slot=1,
      desc="Enjoy international cuisine with panoramic views of the hotel garden.", hours="12:00 - 23:00")
place("2", "sky-lounge", "Sky Lounge", "dining", [4, 5], "n", "public", node_slot=4, desc="Cocktails and small plates above the skyline.", hours="17:00 - 01:00",
      ref={"type": "restaurant", "slug": "sky-lounge"})
for n_, s_ in zip([201, 202], [6, 7]): room("2", n_, s_, "n")
for n_, s_ in zip([203, 204], [0, 1]): room("2", n_, s_, "s")
place("2", "restrooms-2", "Restrooms", "restroom", [2], "s", "service", desc="Restrooms.")
for n_, s_ in zip([205, 206, 207], [5, 6, 7]): room("2", n_, s_, "s")

# ---------------- 3: rooms + suite ----------------
for n_, s_ in zip([301, 302, 303], [0, 1, 2]): room("3", n_, s_, "n")
for n_, s_ in zip([304, 305], [4, 5]): room("3", n_, s_, "n")
place("3", "presidential", "Presidential Suite", "room", [6, 7], "n", "public", node_slot=6, desc="Our finest suite: two bedrooms, a private terrace and butler service.")
for n_, s_ in zip([306, 307], [0, 1]): room("3", n_, s_, "s")
place("3", "restrooms-3", "Restrooms", "restroom", [2], "s", "service", desc="Restrooms.")
for n_, s_ in zip([308, 309, 310], [5, 6, 7]): room("3", n_, s_, "s")

# ---------------- fill any unused slot with a "staff only" service area ----------------
for f in floors:
    for side, (y, h) in (("n", (43, 224)), ("s", (333, 224))):
        for i in range(8):
            centre = cx(i)
            covered = any(a["kind"] not in ("building", "corridor") and a["y"] == y and a["x"] <= centre <= a["x"] + a["w"] for a in f["areas"])
            if not covered:
                f["areas"].append({"id": f"{f['id']}-{side}{i}-staff", "kind": "service", "x": sx(i) + 3, "y": y, "w": SLOT_W - 6, "h": h})

# ---------------- vertical links (elevator c3, stairs c4) ----------------
by_id = {n["id"]: n for n in nodes}
labels = [l for l, _, _ in FLOORS]
for i in range(len(labels) - 1):
    for c in ("c3", "c4"):
        a, b = by_id[f"{fid(labels[i])}-{c}"], by_id[f"{fid(labels[i+1])}-{c}"]
        a["connections"].append(b["id"]); b["connections"].append(a["id"])

data = {"version": 1, "meters_per_unit": 0.1, "default_start": "reception", "floors": floors, "nodes": nodes, "locations": locations}

# ---------------- self-check ----------------
ids = [l["id"] for l in locations]; assert len(ids) == len(set(ids)), "duplicate location ids"
node_ids = {n["id"] for n in nodes}; assert len(node_ids) == len(nodes)
for n in nodes:
    for c in n["connections"]: assert c in node_ids and n["id"] in by_id[c]["connections"], (n["id"], c)
for l in locations: assert l["node"] in node_ids and by_id[l["node"]]["floor"] == l["floor"], l["id"]
seen, q = {"fg-e0"}, deque(["fg-e0"])
while q:
    for c in by_id[q.popleft()]["connections"]:
        if c not in seen: seen.add(c); q.append(c)
assert seen == node_ids, f"unreachable nodes: {sorted(node_ids - seen)}"

out = os.path.join(os.path.dirname(os.path.abspath(__file__)), "demo-hotel-map.json")
with open(out, "w") as fh: json.dump(data, fh, indent=1)
print(f"floors={len(floors)} nodes={len(nodes)} locations={len(locations)} -> {out} ({os.path.getsize(out)//1024} KB)")
